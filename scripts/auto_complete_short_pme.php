<?php
// Daily job: PME rows for short trainings (total training hour <= 4) do not
// need a HOD evaluation, so once their evaluation period has started
// (pme.from_date <= today) any row still 'pending' or 'approved' is set to
// 'completed'.
//
// Training hour uses the same formula as My Training / the PME list:
//   (DATEDIFF(enddate, startdate) + 1) * hours between starttime and endtime
// It is recomputed on every run from training_all, so if admin edits a
// training's dates/times before its evaluation period starts, the new hours
// are what count. Rows already set to 'completed' are not reverted if the
// training is later edited to more than 4 hours.
//
// Before from_date the row is left alone - same as any other PME row.
//
// How it runs:
//  - Automatically, from the top of scripts/notify_pme_incomplete.php (which
//    both the VPS cron and the Windows Task Scheduler URL call), so it always
//    runs before HODs are reminded about pending rows.
//  - CLI: php scripts/auto_complete_short_pme.php [--dry-run]
//    --dry-run lists what would change and writes nothing.
//
// Every changed row is printed (participationid, staffno, old status,
// training, hours), so the reminders log doubles as the undo record.

define('SHORT_PME_MAX_HOURS', 4);

function autoCompleteShortPme(mysqli $conn, bool $dryRun = false): int
{
    $today = date('Y-m-d');
    $maxHours = SHORT_PME_MAX_HOURS;

    $sql = "SELECT pme.id, pme.participationid, pme.staffno, pme.status, pme.training_title,
                   ROUND((DATEDIFF(t.enddate, t.startdate) + 1) * (TIME_TO_SEC(TIMEDIFF(t.endtime, t.starttime)) / 3600), 2) AS totalhour
            FROM pme
            JOIN training_all t ON t.id = pme.trainingid
            WHERE pme.status IN ('pending', 'approved')
              AND pme.from_date <= ?
              AND (DATEDIFF(t.enddate, t.startdate) + 1) * (TIME_TO_SEC(TIMEDIFF(t.endtime, t.starttime)) / 3600) <= ?
            ORDER BY pme.from_date, pme.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $today, $maxHours);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $prefix = "[" . date('Y-m-d H:i:s') . "] Short-training PME auto-complete" . ($dryRun ? " (DRY RUN)" : "");
    if (!$rows) {
        echo "$prefix: nothing to update.\n";
        return 0;
    }

    $update = $conn->prepare("UPDATE pme SET status = 'completed' WHERE id = ? AND status IN ('pending', 'approved')");
    $changed = 0;
    foreach ($rows as $row) {
        if (!$dryRun) {
            $update->bind_param("i", $row['id']);
            $update->execute();
            if ($update->affected_rows < 1) {
                continue;
            }
        }
        $changed++;
        echo "  participationid={$row['participationid']}\tstaffno={$row['staffno']}\t{$row['status']} -> completed\t{$row['totalhour']}h\t{$row['training_title']}\n";
    }
    echo "$prefix: " . ($dryRun ? "would update" : "updated") . " $changed row(s).\n";
    return $changed;
}

// Run directly from the command line only; when included by another script
// that script calls autoCompleteShortPme() itself.
if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require __DIR__ . '/../dbconn.php';
    date_default_timezone_set('Asia/Kuala_Lumpur');
    autoCompleteShortPme($conn, in_array('--dry-run', $argv, true));
}
