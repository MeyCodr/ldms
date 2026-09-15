<?php
// One-shot production deploy script for the dashboard performance indexes
// built and verified locally on 2026-09-16, but only ever applied to the
// LOCAL database - uploading files never carries schema/index changes with
// it, the same reason scripts/deploy_pme_not_applicable_20260915.php had to
// exist. This one just adds indexes - it never touches or reshapes any
// existing data, so it is lower-risk than that script, but a production
// dashboard querying 300k+ row tables with zero non-primary-key indexes is
// still worth fixing directly rather than waiting on a routine backup/deploy.
//
// WHAT THIS FIXES (and does not fix)
//   The HOD dashboard's per-department charts and several other dashboard
//   queries join/filter on these columns with NO index at all today, so
//   MySQL full-scans training/participation/ojt/participateojt (live AND
//   _archive) on every load. This script adds one index per column so those
//   individual table scans become index scans.
//   It does NOT fix the ~2s "Average Total Hour" division-wide charts
//   (Top 10 / Business / DHMSB / Quality / Finance / Human / R&D /
//   Operation) - those re-materialize a combined ~233k-row UNION of public
//   + OJT participation into an unindexed temp table before the final
//   GROUP BY, which no base-table index can speed up. That needs a bigger
//   restructuring (compute the shared dataset once instead of 8 times) -
//   deliberately out of scope for this script.
//
// INDEXES ADDED (18 total - one CREATE INDEX per row, skipped if it already
// exists so this is safe to re-run):
//   training(startdate), training_archive(startdate)
//   participation(trainingid, userid, attendance) and the same 3 on participation_archive
//   ojt(startdate), ojt_archive(startdate)
//   participateojt(ojtid, userid, attendance) and the same 3 on participateojt_archive
//   user(department, division, dateresign)
//
// SAFETY
//   - Dry run by default: reports which indexes are missing, creates
//     nothing. Pass --apply to actually create them.
//   - Purely additive - CREATE INDEX cannot change any existing data or
//     query results, only speed. Nothing to back up or undo beyond
//     DROP INDEX if one were ever unwanted (unlikely - these are narrow,
//     single-column indexes, cheap to keep).
//   - Idempotent - checks SHOW INDEX for the exact key name before creating
//     it, so running this twice (or after a partial run) only creates what's
//     still missing.
//   - participateojt_archive has ~250k+ rows - its 3 indexes took under a
//     second each locally, but plan for this to take longer than the
//     smaller tables on a busier production server.
//
// USAGE
//  - CLI (php scripts/deploy_dashboard_indexes_20260916.php [--apply]) -
//    no token needed, CLI access already implies server access.
//  - HTTP, for cPanel hosts with no terminal/SSH access - upload this file
//    normally, then visit it in a browser with the matching ?key=:
//      https://<your-domain>/ldms/scripts/deploy_dashboard_indexes_20260916.php?key=14d7153a01f2ec0dca523bf4678ed0e8ee2aafeb55f58958
//      https://<your-domain>/ldms/scripts/deploy_dashboard_indexes_20260916.php?key=...&apply=1
//    Change DEPLOY_INDEXES_TOKEN below before relying on this - the value
//    here is a placeholder generated for setup, not a secret kept out of
//    source control. Delete this file from the server once you're done
//    with it; it is a one-time fix, not something that needs to stay
//    reachable.
define('DEPLOY_INDEXES_TOKEN', '14d7153a01f2ec0dca523bf4678ed0e8ee2aafeb55f58958');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(DEPLOY_INDEXES_TOKEN, (string) $_GET['key'])) {
        http_response_code(403);
        header('Content-Type: text/plain');
        die('Forbidden');
    }
    header('Content-Type: text/plain');
    @set_time_limit(0);
    @ignore_user_abort(true);
}

require __DIR__ . '/../dbconn.php';

$apply = (PHP_SAPI === 'cli')
    ? in_array('--apply', $argv, true)
    : (isset($_GET['apply']) && $_GET['apply'] === '1');

echo "==============================================================\n";
echo "Dashboard index deploy - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

$indexes = [
    ['training', 'idx_training_startdate', 'startdate'],
    ['training_archive', 'idx_training_archive_startdate', 'startdate'],
    ['participation', 'idx_participation_trainingid', 'trainingid'],
    ['participation', 'idx_participation_userid', 'userid'],
    ['participation', 'idx_participation_attendance', 'attendance'],
    ['participation_archive', 'idx_participation_archive_trainingid', 'trainingid'],
    ['participation_archive', 'idx_participation_archive_userid', 'userid'],
    ['participation_archive', 'idx_participation_archive_attendance', 'attendance'],
    ['ojt', 'idx_ojt_startdate', 'startdate'],
    ['ojt_archive', 'idx_ojt_archive_startdate', 'startdate'],
    ['participateojt', 'idx_participateojt_ojtid', 'ojtid'],
    ['participateojt', 'idx_participateojt_userid', 'userid'],
    ['participateojt', 'idx_participateojt_attendance', 'attendance'],
    ['participateojt_archive', 'idx_participateojt_archive_ojtid', 'ojtid'],
    ['participateojt_archive', 'idx_participateojt_archive_userid', 'userid'],
    ['participateojt_archive', 'idx_participateojt_archive_attendance', 'attendance'],
    ['user', 'idx_user_department', 'department'],
    ['user', 'idx_user_division', 'division'],
    ['user', 'idx_user_dateresign', 'dateresign'],
];

$created = 0;
$skipped = 0;
$failed = 0;

foreach ($indexes as [$table, $name, $col]) {
    $existing = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$name'");
    if ($existing && $existing->num_rows > 0) {
        echo "SKIP  {$table}.{$col} ({$name} already exists)\n";
        $skipped++;
        continue;
    }

    if (!$apply) {
        echo "MISSING  {$table}.{$col} - would create {$name} (re-run with --apply)\n";
        continue;
    }

    $start = microtime(true);
    $ok = $conn->query("CREATE INDEX `$name` ON `$table` (`$col`)");
    $elapsed = microtime(true) - $start;
    if ($ok) {
        printf("OK    %s.%s (%s) in %.2fs\n", $table, $col, $name, $elapsed);
        $created++;
    } else {
        echo "FAIL  {$table}.{$col} ({$name}): {$conn->error}\n";
        $failed++;
    }
}

echo "\n";
echo "Created: $created, Skipped (already existed): $skipped, Failed: $failed\n";
echo "==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
