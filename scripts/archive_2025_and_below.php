<?php
// Usage:
//   php scripts/archive_2025_and_below.php              (dry run - reports counts only, changes nothing)
//   php scripts/archive_2025_and_below.php --execute     (actually archives + deletes)
//
// Moves all records dated 2025-and-below out of the live tables into matching
// "<table>_archive" tables in the SAME database, then deletes them from the
// live tables. After running with --execute, the live tables will only
// contain 2026-onward records.
//
// Strongly recommended: take a full `mysqldump` backup of the database before
// running with --execute, even though this script also keeps an archive copy.
//
// Table groups (children are archived/deleted before their parent so nothing
// is left orphaned):
//   training family : session, participation, training   (by training.startdate)
//   ojt family       : participateojt, ojt                (by ojt.startdate)
//   tna                                                    (by tna.dateapprove, falling back to tna.year when dateapprove is NULL)
//   pme                                                     (by pme.from_date)
//   tni                                                     (by tni.year)
//   certificate                                             (by certificate.upload_date)

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line (php scripts/archive_2025_and_below.php), not over HTTP.\n");
}

require_once __DIR__ . '/../dbconn.php';

if (!($conn instanceof mysqli)) {
    die("dbconn.php must expose a mysqli \$conn object for this script.\n");
}

$execute = in_array('--execute', $argv, true);

echo $execute ? "MODE: EXECUTE (tables will be modified)\n" : "MODE: DRY RUN (no changes will be made; pass --execute to apply)\n";
echo str_repeat('=', 70) . "\n";

function archiveTable($conn, $execute, $liveTable, $whereSql, $label)
{
    $countRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM `$liveTable` WHERE $whereSql"));
    $count = (int) $countRow['c'];
    echo "[$label] $liveTable: $count row(s) match the archive condition\n";

    if (!$execute || $count === 0) {
        return $count;
    }

    $archiveTable = $liveTable . '_archive';

    mysqli_begin_transaction($conn);
    try {
        $ok = mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$archiveTable` LIKE `$liveTable`");
        if (!$ok) {
            throw new Exception("create archive table failed: " . mysqli_error($conn));
        }

        $ok = mysqli_query($conn, "INSERT IGNORE INTO `$archiveTable` SELECT * FROM `$liveTable` WHERE $whereSql");
        if (!$ok) {
            throw new Exception("copy to archive failed: " . mysqli_error($conn));
        }
        $copied = mysqli_affected_rows($conn);

        $ok = mysqli_query($conn, "DELETE FROM `$liveTable` WHERE $whereSql");
        if (!$ok) {
            throw new Exception("delete from live table failed: " . mysqli_error($conn));
        }
        $deleted = mysqli_affected_rows($conn);

        mysqli_commit($conn);
        echo "  -> archived $copied row(s), deleted $deleted row(s) from $liveTable\n";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "  !! ROLLED BACK ($liveTable): " . $e->getMessage() . "\n";
    }

    return $count;
}

// --- training family --------------------------------------------------
$trainingIdsRes = mysqli_query($conn, "SELECT id FROM training WHERE YEAR(startdate) <= 2025");
$trainingIds = [];
while ($row = mysqli_fetch_assoc($trainingIdsRes)) {
    $trainingIds[] = (int) $row['id'];
}
$trainingIdList = empty($trainingIds) ? '0' : implode(',', $trainingIds);

archiveTable($conn, $execute, 'session', "trainingid IN ($trainingIdList)", 'training family');
archiveTable($conn, $execute, 'participation', "trainingid IN ($trainingIdList)", 'training family');
archiveTable($conn, $execute, 'training', "id IN ($trainingIdList)", 'training family');

// --- ojt family ---------------------------------------------------------
$ojtIdsRes = mysqli_query($conn, "SELECT id FROM ojt WHERE YEAR(startdate) <= 2025");
$ojtIds = [];
while ($row = mysqli_fetch_assoc($ojtIdsRes)) {
    $ojtIds[] = (int) $row['id'];
}
$ojtIdList = empty($ojtIds) ? '0' : implode(',', $ojtIds);

archiveTable($conn, $execute, 'participateojt', "ojtid IN ($ojtIdList)", 'ojt family');
archiveTable($conn, $execute, 'ojt', "id IN ($ojtIdList)", 'ojt family');

// --- standalone tables ---------------------------------------------------
archiveTable($conn, $execute, 'tna', "(dateapprove IS NOT NULL AND YEAR(dateapprove) <= 2025) OR (dateapprove IS NULL AND CAST(year AS UNSIGNED) <= 2025)", 'tna');
archiveTable($conn, $execute, 'pme', "YEAR(from_date) <= 2025", 'pme');
archiveTable($conn, $execute, 'tni', "CAST(year AS UNSIGNED) <= 2025", 'tni');
archiveTable($conn, $execute, 'certificate', "upload_date IS NOT NULL AND YEAR(upload_date) <= 2025", 'certificate');

echo str_repeat('=', 70) . "\n";
echo $execute ? "Done.\n" : "Dry run complete. Re-run with --execute to apply.\n";
