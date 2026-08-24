<?php
/**
 * Merge duplicate staff records that are the same person.
 *
 *   php merge_duplicate_staff.php            -> dry run, changes nothing
 *   php merge_duplicate_staff.php --apply    -> writes, inside one transaction
 *
 * For each group every reference to a losing id is repointed at the surviving
 * id, optional profile fields are adopted from a loser, and the losing rows
 * are then deleted.
 *
 * Deleting a user CASCADEs into skill_matrix_evaluations (fk_sme_staffid),
 * which is why matrices are repointed BEFORE the delete, never after.
 *
 * Only groups confirmed to be the same person belong here. Staff numbers
 * shared by two DIFFERENT employees must be renumbered by HR instead.
 */

$apply = in_array('--apply', $argv, true);

include __DIR__ . '/../dbconn.php';

/*
 * survivor : id that stays
 * losers   : ids merged into it and then deleted
 * adopt    : profile columns to copy from a loser onto the survivor
 * drop_evaluations : evaluation ids to delete rather than repoint, used when
 *                    the same evaluation was entered twice, once per copy
 */
$groups = [
    ['staffno' => 'APZA200', 'survivor' => 2894, 'losers' => [5616],
     'adopt' => ['from' => 5616, 'columns' => ['department', 'department_id', 'section', 'section_id', 'division', 'division_id']],
     'note' => 'transfer to QUALITY ASSURANCE & CONTROL SA1; keep old id for its 32 training records'],

    ['staffno' => 'AQ0641', 'survivor' => 4278, 'losers' => [4373],
     'note' => 'survivor has the correctly spelled name'],

    ['staffno' => 'KA215', 'survivor' => 3964, 'losers' => [4806]],

    ['staffno' => 'KM00198', 'survivor' => 3619, 'losers' => [3700],
     'note' => 'loser name is padded with tab characters'],

    ['staffno' => 'KM00302', 'survivor' => 4097, 'losers' => [4220],
     'note' => 'survivor is the ACTIVE record'],

    ['staffno' => 'SAC051', 'survivor' => 5462, 'losers' => [5463],
     'drop_evaluations' => [254],
     'note' => 'evaluation 254 is a byte-identical re-entry of 253, so it is dropped not repointed'],

    ['staffno' => 'SON009', 'survivor' => 2850, 'losers' => [5425]],

    ['staffno' => 'WB147', 'survivor' => 3716, 'losers' => [3812, 3814]],

    ['staffno' => 'WE0322', 'survivor' => 4080, 'losers' => [4515, 4539]],

    ['staffno' => 'ZG0149', 'survivor' => 3681, 'losers' => [3753]],

    ['staffno' => 'ZG0352', 'survivor' => 4663, 'losers' => [4705]],

    ['staffno' => 'ZG0420', 'survivor' => 4895, 'losers' => [5449]],

    ['staffno' => 'ZG0456', 'survivor' => 5448, 'losers' => [5856]],

    ['staffno' => 'ZG0472', 'survivor' => 5447, 'losers' => [5861],
     'adopt' => ['from' => 5861, 'columns' => ['staffname']],
     'note' => 'loser has the name without trailing whitespace'],

    ['staffno' => 'ZGNS122', 'survivor' => 2735, 'losers' => [5545],
     'adopt' => ['from' => 5545, 'columns' => ['department', 'department_id', 'section', 'section_id', 'division', 'division_id']],
     'note' => 'transfer to QUALITY ASSURANCE & CONTROL SA1; keep old id for its 328 training records'],

    /* ---- round 2: same person, name recorded two different ways ----
     * Survivor is the record carrying the fuller / better spelled name, even
     * where the other copy holds more history - history is repointed either
     * way, but the name that survives is the one people will read. */

    ['staffno' => 'SL159', 'survivor' => 4794, 'losers' => [4833],
     'note' => 'survivor name includes BIN: MUHAMMAD SHAHAIKAL BIN MARTHA HADINATA'],

    ['staffno' => 'SON013', 'survivor' => 2857, 'losers' => [5461],
     'note' => 'MAHATO SHYAM KISHOR vs MOHATO SHYAM KISHAR; survivor also holds the 49 training records'],

    ['staffno' => 'T5812', 'survivor' => 1467, 'losers' => [5845],
     'note' => 'MOHD LAQMAN HAMKA BIN MAT SHAH vs MALSHAH; survivor holds 145 records and the matrix'],

    ['staffno' => 'WB122', 'survivor' => 3226, 'losers' => [3358],
     'note' => 'DHARSHANAN A/L GANESAN vs GANESON'],

    ['staffno' => 'WB127', 'survivor' => 3303, 'losers' => [3359],
     'note' => 'YUMAREJEN A/L GANESEN vs GANSEN'],

    ['staffno' => 'WB128', 'survivor' => 3302, 'losers' => [4344],
     'note' => 'survivor name includes the surname: MUHAMMAD HARUN B PUSPANATHAN'],

    ['staffno' => 'YS052', 'survivor' => 3013, 'losers' => [3481],
     'drop_evaluations' => [1369],
     'note' => 'MUHAMMAD ISKANDAR vs ISKANDAR; eval 1369 is an identical draft of 1479, dropped not repointed'],

    ['staffno' => 'ZGN039', 'survivor' => 2921, 'losers' => [3088],
     'note' => 'survivor name includes the surname BISHWAKARMA; first name UM vs OM still needs HR to confirm'],

    /* ---- round 3: same person, the idle copy simply goes ----
     * These were originally a plain delete because the second copy held
     * nothing at all. On the current production data two of them have since
     * had a skill matrix drafted against BOTH copies, so they are merged
     * here instead - deleting would have destroyed a matrix via the
     * fk_sme_staffid cascade. */

    ['staffno' => 'SPMT010', 'survivor' => 4541, 'losers' => [4649],
     'note' => 'loser holds no records at all'],

    ['staffno' => 'WA170', 'survivor' => 4298, 'losers' => [4450],
     'note' => 'loser holds no records at all'],

    ['staffno' => 'WB119', 'survivor' => 3223, 'losers' => [3357],
     'note' => 'loser holds no records at all'],

    ['staffno' => 'KA275', 'survivor' => 5441, 'losers' => [5204],
     'drop_evaluations' => [1535],
     'note' => 'survivor holds the 9 OJT records; eval 1535 is an identical draft of 1536, dropped not repointed'],

    ['staffno' => 'YS081', 'survivor' => 3574, 'losers' => [3629],
     'drop_evaluations' => [1437],
     'note' => 'eval 1437 is an identical draft of 1436, dropped not repointed'],

    /* ---- round 4: byte-identical names inside a group that also contains a
     * genuinely different person. Only the identical pair is merged; the
     * third record is left alone for HR to renumber, so these staff numbers
     * stay duplicated afterwards - but as a clean pair rather than a
     * three-way tangle. */

    ['staffno' => 'APZB243', 'survivor' => 4346, 'losers' => [4495],
     'note' => 'identical to 4346; 4349 MUHAMMAD HAFIZ B SUKRAN is a different person and is NOT touched'],

    ['staffno' => 'ZG0215', 'survivor' => 4516, 'losers' => [4540],
     'note' => 'identical to 4516; 4095 BARATH RAY is a different spelling and is NOT touched'],

    /* ---- round 5: HR-confirmed merges, 2026-08-24 ----
     * HR returned duplicate_staffno_for_hr_updated.csv assigning a corrected
     * staff number to one record of each pair. For these five the assigned
     * number was already held by the SAME person, i.e. the employee exists
     * twice, so they are merges rather than renames.
     *
     * Survivor = the record holding more history, which minimises how much
     * has to be repointed. It then takes the staff number and status HR
     * specified. The end state is identical either way, since every table
     * links by user.id. */

    ['staffno' => 'AQ0792', 'survivor' => 4828, 'losers' => [4827],
     'expect' => [4828 => 'AQ0792', 4827 => 'AQ0729'],
     'set' => ['status' => 'RESIGN'],
     'note' => 'LOGABALAN A/L NADARAJAH; survivor already holds HR\'s number and its 24 training records'],

    ['staffno' => 'T6594', 'survivor' => 5670, 'losers' => [5671],
     'expect' => [5670 => 'T6494', 5671 => 'T6594'],
     'set' => ['staffno' => 'T6594'],
     'note' => 'MUHAMAD DANISH FARHAN BIN MD ROZY; survivor keeps its 4 records and date_join, takes HR\'s number'],

    ['staffno' => 'WE0308', 'survivor' => 3688, 'losers' => [3762],
     'expect' => [3688 => 'WE0308', 3762 => 'ZG0158'],
     'set' => ['status' => 'RESIGN'],
     'note' => 'THARUN A/L SURENDAR; survivor already holds HR\'s number and its 84 training records'],

    ['staffno' => 'T6551', 'survivor' => 5385, 'losers' => [5750],
     'expect' => [5385 => 'T6552', 5750 => 'T6551'],
     'set' => ['staffno' => 'T6551'],
     'note' => 'FARRISHAH AQASHAR BIN FAZLI; survivor keeps its 15 records, HR-corrected spelling and the TM2 (OSI) department HR confirmed'],

    ['staffno' => 'ZG0215', 'survivor' => 4095, 'losers' => [4516],
     'note' => 'BARATH RAY A/L KARPANAN; HR\'s name correction makes both records the same person, survivor holds 71 records'],
];

/* Every column that points at user.id, and what to do with it. */
$repoint = [
    ['participation',            'userid'],
    ['participation_archive',    'userid'],
    ['participateojt',           'userid'],
    ['participateojt_archive',   'userid'],
    ['participateojt',           'clerkid'],
    ['participateojt_archive',   'clerkid'],
    ['tna',                      'userid'],
    ['tna_archive',              'userid'],
    ['pme',                      'userid'],
    ['pme_archive',              'userid'],
    ['pme',                      'hodid'],
    ['pme_archive',              'hodid'],
    ['pme',                      'verified_by'],
    ['pme_archive',              'verified_by'],
    ['skill_matrix_evaluations', 'staffid'],
    ['skill_matrix_evaluations', 'created_by'],
    ['skill_matrix_evaluations', 'approved_by'],
    ['user',                     'hodid'],
    ['departments',              'hod_user_id'],
];

echo $apply ? "=== APPLYING ===\n\n" : "=== DRY RUN - nothing will be written ===\n\n";

$conn->begin_transaction();

$totalMoved = 0;
$totalDeleted = 0;
$totalDropped = 0;
$failed = false;

foreach ($groups as $g) {
    $survivor = (int) $g['survivor'];
    $losers = array_map('intval', $g['losers']);
    $list = implode(',', $losers);

    echo "{$g['staffno']}: keep id {$survivor}, merge " . implode(', ', $losers) . "\n";
    if (!empty($g['note'])) {
        echo "    note: {$g['note']}\n";
    }

    // Sanity: every id must exist and share the staff number.
    $check = $conn->query("SELECT id, staffno FROM user WHERE id IN ({$survivor},{$list})");
    $found = [];
    while ($r = $check->fetch_assoc()) { $found[(int) $r['id']] = $r['staffno']; }

    // Already merged on an earlier run: survivor is there, losers are gone.
    // Skip rather than abort, so this file stays a re-runnable record of every
    // merge that has been done.
    $losersPresent = 0;
    foreach ($losers as $id) { if (isset($found[$id])) $losersPresent++; }
    if ($losersPresent === 0 && isset($found[$survivor])) {
        echo "    already merged, skipped\n\n";
        continue;
    }

    // Normally every id in a group shares the group's staff number. Where the
    // duplicate was entered under a *different* number - the same person
    // recorded twice - 'expect' names the number each id should currently
    // hold, so the guard still verifies we have the right records.
    foreach (array_merge([$survivor], $losers) as $id) {
        $want = isset($g['expect'][$id]) ? $g['expect'][$id] : $g['staffno'];
        if (!isset($found[$id])) {
            echo "    ABORT: id {$id} does not exist\n";
            $failed = true;
        } elseif ($found[$id] !== $want) {
            echo "    ABORT: id {$id} has staffno '{$found[$id]}', expected '{$want}'\n";
            $failed = true;
        }
    }
    if ($failed) break;

    // Same-quarter re-entries are dropped rather than moved onto the survivor.
    if (!empty($g['drop_evaluations'])) {
        $ids = implode(',', array_map('intval', $g['drop_evaluations']));
        $conn->query("DELETE FROM skill_matrix_evaluations WHERE id IN ({$ids})");
        $n = $conn->affected_rows;
        $totalDropped += $n;
        echo "    dropped duplicate evaluation(s) {$ids}: {$n} row(s)\n";
    }

    foreach ($repoint as [$table, $column]) {
        $conn->query("UPDATE `{$table}` SET `{$column}` = {$survivor} WHERE `{$column}` IN ({$list})");
        $n = $conn->affected_rows;
        if ($n > 0) {
            $totalMoved += $n;
            echo "    {$table}.{$column}: {$n} row(s) moved\n";
        }
    }

    // Literal values HR specified for the surviving record.
    if (!empty($g['set'])) {
        $sets = []; $types = ''; $params = [];
        foreach ($g['set'] as $col => $val) {
            $sets[] = "`{$col}` = ?"; $types .= 's'; $params[] = $val;
        }
        $types .= 'i'; $params[] = $survivor;
        $stmt = $conn->prepare("UPDATE user SET " . implode(', ', $sets) . " WHERE id = ?");
        $refs = [$types];
        foreach ($params as $k => $v) { $refs[] = &$params[$k]; }
        call_user_func_array([$stmt, 'bind_param'], $refs);
        $stmt->execute();
        foreach ($g['set'] as $col => $val) {
            echo "    set {$col} = {$val}\n";
        }
    }

    if (!empty($g['adopt'])) {
        $from = (int) $g['adopt']['from'];
        $cols = $g['adopt']['columns'];
        $sets = [];
        foreach ($cols as $c) { $sets[] = "s.`{$c}` = l.`{$c}`"; }
        $conn->query("UPDATE user s JOIN user l ON l.id = {$from} SET " . implode(', ', $sets) . " WHERE s.id = {$survivor}");
        echo "    adopted from {$from}: " . implode(', ', $cols) . "\n";
    }

    $conn->query("DELETE FROM user WHERE id IN ({$list})");
    $n = $conn->affected_rows;
    $totalDeleted += $n;
    echo "    deleted {$n} duplicate row(s)\n\n";
}

$dupes = $conn->query("SELECT COUNT(*) c FROM (SELECT staffno FROM user WHERE staffno<>'' GROUP BY staffno HAVING COUNT(*)>1) d")->fetch_assoc()['c'];
$users = $conn->query("SELECT COUNT(*) c FROM user")->fetch_assoc()['c'];

echo "references moved : {$totalMoved}\n";
echo "duplicate evals dropped: {$totalDropped}\n";
echo "rows deleted     : {$totalDeleted}\n";
echo "users            : {$users}\n";
echo "duplicate staffno remaining: {$dupes}\n";

if ($failed) {
    $conn->rollback();
    echo "\nABORTED - rolled back, nothing changed.\n";
    exit(1);
}

if ($apply) {
    $conn->commit();
    echo "\nCOMMITTED.\n";
} else {
    $conn->rollback();
    echo "\nDRY RUN - rolled back, nothing changed. Re-run with --apply to write.\n";
}
?>
