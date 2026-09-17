<?php
// One-shot production deploy script for the two OJT "covering" indexes that
// back the Technique B dashboard rewrite done 2026-09-15 (see
// scripts/deploy_dashboard_indexes_20260916.php for the precedent pattern
// this follows, and the fetch_dash.php diffs in staff/hod/ and admin/ for
// the queries that actually use these indexes).
//
// WHAT THIS FIXES
//   The company-wide (not department-scoped) OJT dashboard charts -
//   fetch_top10 (HOD only)/fetch_business/fetch_dhmsb/fetch_finance/
//   fetch_human/fetch_operation/fetch_transform/fetch_quality/fetch_rnd in
//   both staff/hod/fetch_dash.php and admin/fetch_dash.php - were rewritten
//   to bypass the participateojt_all/ojt_all UNION ALL views with narrow
//   inline UNIONs plus FORCE INDEX on these two new indexes. Without the
//   index, MySQL still full-scans participateojt/participateojt_archive via
//   the old single-column attendance index and does a row lookup per match;
//   with it, the ojtid/userid/totalman the query needs are answered
//   straight from the index, never touching the base table row. Verified
//   locally: ~4-5x faster (3.5s -> 0.7-0.9s per chart query).
//
// INDEXES ADDED (2 total, skipped if already present so this is safe to
// re-run):
//   participateojt(attendance, ojtid, userid, totalman)
//   participateojt_archive(attendance, ojtid, userid, totalman)
//
// SAFETY
//   - Dry run by default: reports which indexes are missing, creates
//     nothing. Pass --apply to actually create them.
//   - Purely additive - CREATE INDEX cannot change any existing data or
//     query results, only speed. Nothing to back up or undo beyond
//     DROP INDEX if one were ever unwanted.
//   - Idempotent - checks SHOW INDEX for the exact key name before creating
//     it, so running this twice (or after a partial run) only creates what's
//     still missing.
//   - participateojt_archive has ~250k+ rows - plan for this to take longer
//     than participateojt (~59k rows) on a busier production server.
//
// USAGE
//  - CLI (php scripts/deploy_ojt_covering_indexes_20260915.php [--apply]) -
//    no token needed, CLI access already implies server access.
//  - HTTP, for cPanel hosts with no terminal/SSH access - upload this file
//    normally, then visit it in a browser with the matching ?key=:
//      https://<your-domain>/ldms/scripts/deploy_ojt_covering_indexes_20260915.php?key=8f3c1a9d6e2b47f0a5c9d8e7b6a4f3c2e1d0b9a8
//      https://<your-domain>/ldms/scripts/deploy_ojt_covering_indexes_20260915.php?key=...&apply=1
//    Change DEPLOY_INDEXES_TOKEN below before relying on this - the value
//    here is a placeholder generated for setup, not a secret kept out of
//    source control. Delete this file from the server once you're done
//    with it; it is a one-time fix, not something that needs to stay
//    reachable.
define('DEPLOY_INDEXES_TOKEN', '8f3c1a9d6e2b47f0a5c9d8e7b6a4f3c2e1d0b9a8');

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
echo "OJT covering index deploy - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

$indexes = [
    ['participateojt', 'idx_cover_participateojt', 'attendance, ojtid, userid, totalman'],
    ['participateojt_archive', 'idx_cover_participateojt_archive', 'attendance, ojtid, userid, totalman'],
];

$created = 0;
$skipped = 0;
$failed = 0;

foreach ($indexes as [$table, $name, $cols]) {
    $existing = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$name'");
    if ($existing && $existing->num_rows > 0) {
        echo "SKIP  {$table} ({$name} already exists)\n";
        $skipped++;
        continue;
    }

    if (!$apply) {
        echo "MISSING  {$table} - would create {$name} ($cols) (re-run with --apply)\n";
        continue;
    }

    $colList = implode(', ', array_map(function ($c) {
        return "`" . trim($c) . "`";
    }, explode(',', $cols)));

    $start = microtime(true);
    $ok = $conn->query("CREATE INDEX `$name` ON `$table` ($colList)");
    $elapsed = microtime(true) - $start;
    if ($ok) {
        printf("OK    %s (%s) in %.2fs\n", $table, $name, $elapsed);
        $created++;
    } else {
        echo "FAIL  {$table} ({$name}): {$conn->error}\n";
        $failed++;
    }
}

echo "\n";
echo "Created: $created, Skipped (already existed): $skipped, Failed: $failed\n";
echo "==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
