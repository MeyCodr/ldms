<?php
session_start();
include "../../tna_training_options.php";

header('Content-Type: application/json; charset=utf-8');

function respond($data)
{
    if (ob_get_length()) ob_clean();
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['fullname']) || $_SESSION['role'] != 'ADMIN' || !isset($_POST['btn_action'])) {
    respond(['message' => 'error', 'detail' => 'Unauthorized']);
}

$action = $_POST['btn_action'];
$sections = tna_training_sections();
$by = $_SESSION['fullname'];

function clean_name($raw)
{
    [$name, $error] = tna_training_clean_name($raw);
    if ($error) respond(['message' => 'error', 'detail' => $error]);
    return $name;
}

function valid_section($sections)
{
    $section = $_POST['section'] ?? '';
    if (!isset($sections[$section])) respond(['message' => 'error', 'detail' => 'Invalid section.']);
    return $section;
}

function fetch_one($conn, $sql, $types, ...$params)
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function get_option($conn, $id)
{
    $row = fetch_one($conn, "SELECT * FROM tna_training_option WHERE id = ?", 'i', $id);
    if (!$row) respond(['message' => 'error', 'detail' => 'Option not found.']);
    return $row;
}

function get_category($conn, $id)
{
    $row = fetch_one($conn, "SELECT * FROM tna_training_category WHERE id = ?", 'i', $id);
    if (!$row) respond(['message' => 'error', 'detail' => 'Group not found.']);
    return $row;
}

// category_id from the form: 0/empty = no group, otherwise must belong to the section.
function posted_category($conn, $section)
{
    $cid = (int) ($_POST['category_id'] ?? 0);
    if (!$cid) return null;
    $cat = get_category($conn, $cid);
    if ($cat['section'] !== $section) respond(['message' => 'error', 'detail' => 'Group does not belong to this section.']);
    return $cid;
}

function option_exists($conn, $section, $cid, $name, $exceptId = 0)
{
    return (bool) fetch_one(
        $conn,
        "SELECT id FROM tna_training_option WHERE section = ? AND category_id <=> ? AND name = ? AND id <> ?",
        'sisi', $section, $cid, $name, $exceptId
    );
}

function next_sort($conn, $table, $section)
{
    $row = fetch_one($conn, "SELECT COALESCE(MAX(sort_order), 0) + 10 AS n FROM $table WHERE section = ?", 's', $section);
    return (int) $row['n'];
}

// Swap sort_order with the neighbour above/below inside the same list.
function move_row($conn, $table, $row, $dir, $scopeSql, $scopeTypes, $scopeParams)
{
    if ($dir !== 'up' && $dir !== 'down') respond(['message' => 'error', 'detail' => 'Invalid direction.']);
    $cmp = $dir === 'up' ? '<' : '>';
    $ord = $dir === 'up' ? 'DESC' : 'ASC';
    $nb = fetch_one(
        $conn,
        "SELECT id, sort_order FROM $table WHERE $scopeSql AND sort_order $cmp ? ORDER BY sort_order $ord LIMIT 1",
        $scopeTypes . 'i', ...array_merge($scopeParams, [(int) $row['sort_order']])
    );
    if (!$nb) respond(['message' => 'noop']);
    $conn->begin_transaction();
    $stmt = $conn->prepare("UPDATE $table SET sort_order = ? WHERE id = ?");
    $a = (int) $nb['sort_order'];
    $b = (int) $row['id'];
    $stmt->bind_param('ii', $a, $b);
    $stmt->execute();
    $a = (int) $row['sort_order'];
    $b = (int) $nb['id'];
    $stmt->bind_param('ii', $a, $b);
    $stmt->execute();
    $conn->commit();
    respond(['message' => 'update']);
}

// ===== LIST =====

if ($action == 'list') {
    $section = valid_section($sections);

    $cats = [];
    $stmt = $conn->prepare("SELECT id, name FROM tna_training_category WHERE section = ? ORDER BY sort_order, id");
    $stmt->bind_param('s', $section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $cats[] = $row;

    $used = [];
    $stmt = $conn->prepare("SELECT training, COUNT(*) n FROM tna WHERE section = ? GROUP BY training");
    $stmt->bind_param('s', $section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $used[$row['training']] = (int) $row['n'];

    $opts = [];
    $stmt = $conn->prepare("SELECT id, category_id, name, is_active, updated_by, updated_at FROM tna_training_option WHERE section = ? ORDER BY sort_order, id");
    $stmt->bind_param('s', $section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['used'] = $used[$row['name']] ?? 0;
        $opts[] = $row;
    }

    respond([
        'message' => 'ok',
        'categories' => $cats,
        'options' => $opts,
        'preview' => tna_training_options_html_all($conn)[$section],
    ]);
}

// ===== OPTIONS =====

if ($action == 'add_option') {
    $section = valid_section($sections);
    $cid = posted_category($conn, $section);
    $name = clean_name($_POST['name'] ?? '');
    if (option_exists($conn, $section, $cid, $name)) respond(['message' => 'error', 'detail' => 'That option is already in this list.']);
    $sort = next_sort($conn, 'tna_training_option', $section);
    $stmt = $conn->prepare("INSERT INTO tna_training_option (section, category_id, name, sort_order, updated_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sisis', $section, $cid, $name, $sort, $by);
    respond($stmt->execute() ? ['message' => 'insert'] : ['message' => 'error', 'detail' => $conn->error]);
}

// Rename and/or move to another group. A rename also updates the TNA rows
// already saved with the old name, so they keep matching an option.
if ($action == 'edit_option') {
    $opt = get_option($conn, (int) ($_POST['id'] ?? 0));
    $section = $opt['section'];
    $cid = posted_category($conn, $section);
    $name = clean_name($_POST['name'] ?? '');
    if (option_exists($conn, $section, $cid, $name, (int) $opt['id'])) respond(['message' => 'error', 'detail' => 'That option is already in this list.']);

    $oldCid = $opt['category_id'] === null ? null : (int) $opt['category_id'];
    $sort = $oldCid === $cid ? (int) $opt['sort_order'] : next_sort($conn, 'tna_training_option', $section);

    $conn->begin_transaction();
    $stmt = $conn->prepare("UPDATE tna_training_option SET name = ?, category_id = ?, sort_order = ?, updated_by = ? WHERE id = ?");
    $id = (int) $opt['id'];
    $stmt->bind_param('siisi', $name, $cid, $sort, $by, $id);
    $stmt->execute();

    $renamed = 0;
    // Only carry saved rows over if no other entry in this section still
    // uses the old name (e.g. the same training listed under two groups).
    $stillListed = fetch_one($conn, "SELECT id FROM tna_training_option WHERE section = ? AND name = ? AND id <> ?", 'ssi', $section, $opt['name'], $id);
    if ($name !== $opt['name'] && !$stillListed) {
        $stmt = $conn->prepare("UPDATE tna SET training = ? WHERE section = ? AND training = ?");
        $stmt->bind_param('sss', $name, $section, $opt['name']);
        $stmt->execute();
        $renamed = $stmt->affected_rows;
    }
    $conn->commit();
    respond(['message' => 'update', 'renamed' => $renamed]);
}

if ($action == 'toggle_option') {
    $opt = get_option($conn, (int) ($_POST['id'] ?? 0));
    $active = (int) $opt['is_active'] ? 0 : 1;
    $id = (int) $opt['id'];
    $stmt = $conn->prepare("UPDATE tna_training_option SET is_active = ?, updated_by = ? WHERE id = ?");
    $stmt->bind_param('isi', $active, $by, $id);
    respond($stmt->execute() ? ['message' => 'update', 'is_active' => $active] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'delete_option') {
    $opt = get_option($conn, (int) ($_POST['id'] ?? 0));
    $used = fetch_one($conn, "SELECT COUNT(*) n FROM tna WHERE section = ? AND training = ?", 'ss', $opt['section'], $opt['name']);
    if ($used['n'] > 0) {
        respond(['message' => 'error', 'detail' => "Cannot delete: {$used['n']} saved TNA row(s) use this option. Hide it instead."]);
    }
    $id = (int) $opt['id'];
    $stmt = $conn->prepare("DELETE FROM tna_training_option WHERE id = ?");
    $stmt->bind_param('i', $id);
    respond($stmt->execute() ? ['message' => 'delete'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'move_option') {
    $opt = get_option($conn, (int) ($_POST['id'] ?? 0));
    $cid = $opt['category_id'] === null ? null : (int) $opt['category_id'];
    move_row($conn, 'tna_training_option', $opt, $_POST['dir'] ?? '', 'section = ? AND category_id <=> ?', 'si', [$opt['section'], $cid]);
}

// ===== GROUPS =====

if ($action == 'add_category') {
    $section = valid_section($sections);
    $name = clean_name($_POST['name'] ?? '');
    if (fetch_one($conn, "SELECT id FROM tna_training_category WHERE section = ? AND name = ?", 'ss', $section, $name)) {
        respond(['message' => 'error', 'detail' => 'That group already exists.']);
    }
    $sort = next_sort($conn, 'tna_training_category', $section);
    $stmt = $conn->prepare("INSERT INTO tna_training_category (section, name, sort_order) VALUES (?, ?, ?)");
    $stmt->bind_param('ssi', $section, $name, $sort);
    respond($stmt->execute() ? ['message' => 'insert'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'rename_category') {
    $cat = get_category($conn, (int) ($_POST['id'] ?? 0));
    $name = clean_name($_POST['name'] ?? '');
    $id = (int) $cat['id'];
    if (fetch_one($conn, "SELECT id FROM tna_training_category WHERE section = ? AND name = ? AND id <> ?", 'ssi', $cat['section'], $name, $id)) {
        respond(['message' => 'error', 'detail' => 'That group already exists.']);
    }
    $stmt = $conn->prepare("UPDATE tna_training_category SET name = ? WHERE id = ?");
    $stmt->bind_param('si', $name, $id);
    respond($stmt->execute() ? ['message' => 'update'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'delete_category') {
    $cat = get_category($conn, (int) ($_POST['id'] ?? 0));
    $id = (int) $cat['id'];
    $n = fetch_one($conn, "SELECT COUNT(*) n FROM tna_training_option WHERE category_id = ?", 'i', $id)['n'];
    if ($n > 0) respond(['message' => 'error', 'detail' => "Cannot delete: this group still has $n option(s). Move or delete them first."]);
    $stmt = $conn->prepare("DELETE FROM tna_training_category WHERE id = ?");
    $stmt->bind_param('i', $id);
    respond($stmt->execute() ? ['message' => 'delete'] : ['message' => 'error', 'detail' => $conn->error]);
}

if ($action == 'move_category') {
    $cat = get_category($conn, (int) ($_POST['id'] ?? 0));
    move_row($conn, 'tna_training_category', $cat, $_POST['dir'] ?? '', 'section = ?', 's', [$cat['section']]);
}

respond(['message' => 'error', 'detail' => 'Unknown action.']);
