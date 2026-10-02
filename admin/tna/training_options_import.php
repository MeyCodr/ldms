<?php
// Excel import for the TNA training options (file from training_options_export.php).
//
// Called twice with the same file: mode=preview returns every change the file
// would make (and any row errors) without writing; mode=apply builds the same
// plan again against the current data and applies it in one transaction.
//
// Rules, matching the Training Options page:
//   - blank ID = new option; existing ID = update that option in place
//   - renaming carries saved tna rows over to the new name
//   - rows missing from the file are left untouched (never deleted)
//   - an option cannot change section
//   - any row error blocks the whole import
session_start();
require '../../asset/vendor/autoload.php';
include "../../tna_training_options.php";

header('Content-Type: application/json; charset=utf-8');

function respond($data)
{
    if (ob_get_length()) ob_clean();
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN') {
    respond(['message' => 'error', 'detail' => 'Unauthorized']);
}

$apply = ($_POST['mode'] ?? '') === 'apply';
$by = $_SESSION['fullname'];
$sections = tna_training_sections();

if (empty($_FILES['import_file']['name']) || !is_uploaded_file($_FILES['import_file']['tmp_name'])) {
    respond(['message' => 'error', 'detail' => 'No file uploaded.']);
}
$fileExt = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
if (!in_array($fileExt, ['xls', 'xlsx'])) {
    respond(['message' => 'error', 'detail' => 'Please upload the .xlsx file downloaded from this page.']);
}

try {
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['import_file']['tmp_name']);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($_FILES['import_file']['tmp_name']);
} catch (Throwable $e) {
    respond(['message' => 'error', 'detail' => 'Could not read the file: ' . $e->getMessage()]);
}

$sheet = $spreadsheet->sheetNameExists('Training Options')
    ? $spreadsheet->getSheetByName('Training Options')
    : $spreadsheet->getSheet(0);
$data = $sheet->toArray(null, false, false, false);

$header = array_map(fn($v) => strtoupper(trim((string) $v)), array_slice($data[0] ?? [], 0, 5));
if ($header !== ['ID', 'SECTION', 'GROUP', 'TRAINING NAME', 'STATUS']) {
    respond(['message' => 'error', 'detail' => 'This does not look like the training options file. Please start from "Download Excel" on this page (columns: ID, Section, Group, Training Name, Status).']);
}

// Section accepted as its label ("a. ESG (...)"), key ("esgaware") or letter ("a").
$sectionLookup = [];
foreach ($sections as $key => $label) {
    $sectionLookup[strtolower($label)] = $key;
    $sectionLookup[$key] = $key;
    $sectionLookup[strtolower(substr($label, 0, 1))] = $key;
    $sectionLookup[strtolower(substr($label, 0, 2))] = $key;
}

// ===== CURRENT DATA =====
$opts = [];
$res = $conn->query("SELECT id, section, category_id, name, is_active, sort_order FROM tna_training_option ORDER BY sort_order, id");
while ($r = $res->fetch_assoc()) {
    $r['category_id'] = $r['category_id'] === null ? null : (int) $r['category_id'];
    $opts[(int) $r['id']] = $r;
}
$cats = [];
$catIdByName = [];
$res = $conn->query("SELECT id, section, name, sort_order FROM tna_training_category ORDER BY sort_order, id");
while ($r = $res->fetch_assoc()) {
    $cats[(int) $r['id']] = $r;
    $catIdByName[$r['section']][$r['name']] = (int) $r['id'];
}
$groupName = fn($cid) => $cid === null ? '' : $cats[$cid]['name'];
$label = fn($sec, $grp, $name) => $sections[$sec] . ' › ' . ($grp !== '' ? "$grp › " : '') . $name;

// ===== PARSE ROWS =====
$errors = [];
$items = [];
$seenId = [];
$seenKey = [];
$err = function ($n, $msg) use (&$errors) {
    $errors[] = ['row' => $n, 'detail' => $msg];
};

if (count($data) > 5001) {
    respond(['message' => 'error', 'detail' => 'The file has too many rows (max 5000).']);
}

for ($idx = 1; $idx < count($data); $idx++) {
    $n = $idx + 1;
    $cells = array_map(fn($v) => trim((string) $v), array_slice(array_pad($data[$idx], 5, ''), 0, 5));
    if (implode('', $cells) === '') continue;
    [$rawId, $rawSection, $rawGroup, $rawName, $rawStatus] = $cells;
    $ok = true;

    $id = null;
    if ($rawId !== '') {
        if (!preg_match('/^\d+(\.0+)?$/', $rawId)) {
            $err($n, "ID \"$rawId\" is not valid. Leave ID blank for a new option.");
            $ok = false;
        } else {
            $id = (int) $rawId;
            if (!isset($opts[$id])) {
                $err($n, "ID $id does not exist. Leave ID blank for a new option, and do not copy IDs between rows or files.");
                $ok = false;
            } elseif (isset($seenId[$id])) {
                $err($n, "ID $id is used twice (also on row {$seenId[$id]}).");
                $ok = false;
            } else {
                $seenId[$id] = $n;
            }
        }
    }

    $section = $sectionLookup[strtolower($rawSection)] ?? null;
    if ($section === null) {
        $err($n, $rawSection === '' ? 'Section is required.' : "Unknown section \"$rawSection\".");
        $ok = false;
    } elseif ($id !== null && isset($opts[$id]) && $opts[$id]['section'] !== $section) {
        $err($n, "This option belongs to \"{$sections[$opts[$id]['section']]}\" and cannot be moved to another section. Hide it and add a new row instead.");
        $ok = false;
    }

    $group = '';
    if ($rawGroup !== '') {
        [$group, $gErr] = tna_training_clean_name($rawGroup);
        if ($gErr) {
            $err($n, "Group: $gErr");
            $ok = false;
        }
    }

    [$name, $nErr] = tna_training_clean_name($rawName);
    if ($nErr) {
        $err($n, "Training Name: $nErr");
        $ok = false;
    }

    $status = strtolower($rawStatus);
    if (!in_array($status, ['', 'active', 'hidden'], true)) {
        $err($n, "Status must be Active or Hidden (got \"$rawStatus\").");
        $ok = false;
    }

    if (!$ok) continue;

    $key = "$section|$group|$name";
    if (isset($seenKey[$key])) {
        $err($n, "\"$name\" appears twice in the same section and group (also on row {$seenKey[$key]}).");
        continue;
    }
    $seenKey[$key] = $n;

    $items[] = ['row' => $n, 'id' => $id, 'section' => $section, 'group' => $group, 'name' => $name, 'active' => $status === 'hidden' ? 0 : 1];
}

if (!$items && !$errors) {
    respond(['message' => 'error', 'detail' => 'No option rows found in the file.']);
}

// ===== CHECKS AGAINST OPTIONS NOT IN THE FILE / RENAME CHAINS =====
$renamedFrom = []; // section => [old name => id] for options being renamed
foreach ($items as $it) {
    if ($it['id'] !== null && $opts[$it['id']]['name'] !== $it['name']) {
        $renamedFrom[$it['section']][$opts[$it['id']]['name']] = $it['id'];
    }
}
foreach ($items as $it) {
    foreach ($opts as $oid => $o) {
        if (isset($seenId[$oid]) || $o['section'] !== $it['section']) continue;
        if ($o['name'] === $it['name'] && $groupName($o['category_id']) === $it['group'] && $oid !== $it['id']) {
            $err($it['row'], "\"{$it['name']}\" already exists in this section and group (ID $oid). If you have already imported this file, download a fresh copy and make your changes there.");
        }
    }
    // Renaming onto a name another option is being renamed away from would
    // make the saved-TNA carry-over ambiguous (A->B while B->C, or swaps).
    if ($it['id'] !== null && $opts[$it['id']]['name'] !== $it['name']) {
        $other = $renamedFrom[$it['section']][$it['name']] ?? null;
        if ($other !== null && $other !== $it['id']) {
            $err($it['row'], "\"{$it['name']}\" is the current name of option ID $other, which this file also renames. Do one of the two renames first, import, then do the other.");
        }
    }
}

if ($errors) {
    usort($errors, fn($a, $b) => $a['row'] <=> $b['row']);
    respond(['message' => 'invalid', 'errors' => $errors]);
}

// ===== PLAN =====
$changes = [];
$summary = ['added' => 0, 'renamed' => 0, 'moved' => 0, 'hidden' => 0, 'shown' => 0, 'groups_added' => 0, 'reordered' => [], 'tna_rows' => 0, 'unchanged' => 0];
$bySection = [];
foreach ($items as $it) $bySection[$it['section']][] = $it;

// Final name of every option in each section, to decide whether a renamed
// option's old name is still listed (then saved rows are left alone).
$finalNames = [];
foreach ($opts as $oid => $o) {
    $finalNames[$o['section']][] = isset($seenId[$oid]) ? null : $o['name'];
}
foreach ($items as $it) $finalNames[$it['section']][] = $it['name'];

$tnaRenames = [];
foreach ($bySection as $sec => $list) {
    foreach ($list as $it) {
        if ($it['group'] !== '' && !isset($catIdByName[$sec][$it['group']])) {
            $catIdByName[$sec][$it['group']] = 'new';
            $summary['groups_added']++;
            $changes[] = ['row' => $it['row'], 'type' => 'group', 'text' => "New group: {$sections[$sec]} › {$it['group']}"];
        }
    }
    foreach ($list as $it) {
        $where = $label($sec, $it['group'], $it['name']);
        if ($it['id'] === null) {
            $summary['added']++;
            $changes[] = ['row' => $it['row'], 'type' => 'add', 'text' => "Add: $where" . ($it['active'] ? '' : ' (hidden)')];
            continue;
        }
        $o = $opts[$it['id']];
        $any = false;
        if ($o['name'] !== $it['name']) {
            $any = true;
            $summary['renamed']++;
            $count = 0;
            if (!in_array($o['name'], $finalNames[$sec], true)) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM tna WHERE section = ? AND training = ?");
                $stmt->bind_param('ss', $sec, $o['name']);
                $stmt->execute();
                $count = (int) $stmt->get_result()->fetch_row()[0];
                $tnaRenames[] = [$sec, $o['name'], $it['name']];
            }
            $summary['tna_rows'] += $count;
            $changes[] = ['row' => $it['row'], 'type' => 'rename', 'text' => "Rename: {$o['name']} → {$it['name']}" . ($count ? " ($count saved TNA row(s) updated)" : '')];
        }
        if ($groupName($o['category_id']) !== $it['group']) {
            $any = true;
            $summary['moved']++;
            $from = $groupName($o['category_id']) ?: '(no group)';
            $to = $it['group'] ?: '(no group)';
            $changes[] = ['row' => $it['row'], 'type' => 'move', 'text' => "Move group: {$it['name']}: $from → $to"];
        }
        if ((int) $o['is_active'] !== $it['active']) {
            $any = true;
            $summary[$it['active'] ? 'shown' : 'hidden']++;
            $changes[] = ['row' => $it['row'], 'type' => $it['active'] ? 'show' : 'hide', 'text' => ($it['active'] ? 'Show: ' : 'Hide: ') . $where];
        }
        if (!$any) $summary['unchanged']++;
    }

    // New order: the file's rows, then options of this section not in the file.
    $current = array_keys(array_filter($opts, fn($o) => $o['section'] === $sec));
    $fileIds = array_values(array_filter(array_column($list, 'id'), fn($v) => $v !== null));
    $rest = array_values(array_filter($current, fn($oid) => !isset($seenId[$oid])));
    $newOrderExisting = array_merge($fileIds, $rest);
    // The forms show ungrouped options first, then each group in order, so
    // compare the two orders in that rendered shape (usort is stable).
    $render = function (array $ids, callable $rank) {
        usort($ids, fn($a, $b) => $rank($a) <=> $rank($b));
        return $ids;
    };
    $newGroups = [];
    foreach ($list as $it) if ($it['group'] !== '') $newGroups[$it['group']] = true;
    foreach ($cats as $c) if ($c['section'] === $sec) $newGroups[$c['name']] = true;
    $newGroups = array_keys($newGroups);
    $itemGroup = [];
    foreach ($list as $it) if ($it['id'] !== null) $itemGroup[$it['id']] = $it['group'];
    $curRank = fn($oid) => $opts[$oid]['category_id'] === null ? -1 : (int) $cats[$opts[$oid]['category_id']]['sort_order'];
    $newRank = function ($oid) use ($itemGroup, $opts, $groupName, $newGroups) {
        $g = $itemGroup[$oid] ?? $groupName($opts[$oid]['category_id']);
        return $g === '' ? -1 : array_search($g, $newGroups, true);
    };
    if ($render($current, $curRank) !== $render($newOrderExisting, $newRank)) {
        $summary['reordered'][] = $sections[$sec];
        $changes[] = ['row' => null, 'type' => 'order', 'text' => "Order changed: {$sections[$sec]}"];
    }
}

$hasChanges = $summary['added'] + $summary['renamed'] + $summary['moved'] + $summary['hidden'] + $summary['shown'] + $summary['groups_added'] + count($summary['reordered']) > 0;

if (!$apply) {
    respond(['message' => 'preview', 'summary' => $summary, 'changes' => $changes, 'has_changes' => $hasChanges, 'rows' => count($items)]);
}
if (!$hasChanges) {
    respond(['message' => 'done', 'summary' => $summary]);
}

// ===== APPLY =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn->begin_transaction();

    foreach ($bySection as $sec => $list) {
        // Groups: file order first, then the section's other groups.
        $order = [];
        foreach ($list as $it) if ($it['group'] !== '') $order[$it['group']] = true;
        foreach ($cats as $c) if ($c['section'] === $sec) $order[$c['name']] = true;
        $sort = 0;
        foreach (array_keys($order) as $gname) {
            $sort += 10;
            if ($catIdByName[$sec][$gname] === 'new') {
                $stmt = $conn->prepare("INSERT INTO tna_training_category (section, name, sort_order) VALUES (?, ?, ?)");
                $stmt->bind_param('ssi', $sec, $gname, $sort);
                $stmt->execute();
                $catIdByName[$sec][$gname] = $conn->insert_id;
            } else {
                $cid = $catIdByName[$sec][$gname];
                $stmt = $conn->prepare("UPDATE tna_training_category SET sort_order = ? WHERE id = ?");
                $stmt->bind_param('ii', $sort, $cid);
                $stmt->execute();
            }
        }

        // Options: file order first, then the section's options not in the file.
        $sort = 0;
        foreach ($list as $it) {
            $sort += 10;
            $cid = $it['group'] === '' ? null : $catIdByName[$sec][$it['group']];
            if ($it['id'] === null) {
                $stmt = $conn->prepare("INSERT INTO tna_training_option (section, category_id, name, sort_order, is_active, updated_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sisiis', $sec, $cid, $it['name'], $sort, $it['active'], $by);
                $stmt->execute();
                continue;
            }
            $o = $opts[$it['id']];
            $edited = $o['name'] !== $it['name'] || $o['category_id'] !== $cid || (int) $o['is_active'] !== $it['active'];
            if ($edited) {
                $stmt = $conn->prepare("UPDATE tna_training_option SET name = ?, category_id = ?, is_active = ?, sort_order = ?, updated_by = ? WHERE id = ?");
                $stmt->bind_param('siiisi', $it['name'], $cid, $it['active'], $sort, $by, $it['id']);
            } else {
                $stmt = $conn->prepare("UPDATE tna_training_option SET sort_order = ? WHERE id = ?");
                $stmt->bind_param('ii', $sort, $it['id']);
            }
            $stmt->execute();
        }
        foreach ($opts as $oid => $o) {
            if ($o['section'] !== $sec || isset($seenId[$oid])) continue;
            $sort += 10;
            $stmt = $conn->prepare("UPDATE tna_training_option SET sort_order = ? WHERE id = ?");
            $stmt->bind_param('ii', $sort, $oid);
            $stmt->execute();
        }
    }

    foreach ($tnaRenames as [$sec, $old, $new]) {
        $stmt = $conn->prepare("UPDATE tna SET training = ? WHERE section = ? AND training = ?");
        $stmt->bind_param('sss', $new, $sec, $old);
        $stmt->execute();
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    error_log('training_options_import failed: ' . $e->getMessage());
    respond(['message' => 'error', 'detail' => 'The import failed and nothing was changed. ' . $e->getMessage()]);
}

respond(['message' => 'done', 'summary' => $summary]);
