<?php
// One-time web-triggered version of scripts/archive_2025_and_below.php,
// for hosts where Terminal / SSH / Cron Jobs are not available.
//
// SECURITY: requires a secret token in the URL. Delete this file from the
// server as soon as you're done running it - do not leave it deployed.
//
// Usage:
//   1. Visit this file's URL with ?token=... - shows a DRY RUN preview only,
//      nothing is changed by visiting the page.
//   2. Review the counts, then click "Confirm & Execute" on that page. That
//      submits a POST request, which is the only thing that actually
//      archives + deletes data.

define('ARCHIVE_TOKEN', '0c26b4d12ab67dd2ac2a1d3453c2f70e11157e533c5f9fad');

$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
if (!hash_equals(ARCHIVE_TOKEN, $token)) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

require_once __DIR__ . '/../dbconn.php';

if (!($conn instanceof mysqli)) {
    die("dbconn.php must expose a mysqli \$conn object for this script.");
}

function archiveTable($conn, $execute, $liveTable, $whereSql, $label)
{
    $countRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM `$liveTable` WHERE $whereSql"));
    $count = (int) $countRow['c'];
    $lines = [];
    $lines[] = "[$label] $liveTable: $count row(s) match the archive condition";

    if (!$execute || $count === 0) {
        return $lines;
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
        $lines[] = "  -> archived $copied row(s), deleted $deleted row(s) from $liveTable";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $lines[] = "  !! ROLLED BACK ($liveTable): " . $e->getMessage();
    }

    return $lines;
}

function runArchive($conn, $execute)
{
    $output = [];

    $trainingIdsRes = mysqli_query($conn, "SELECT id FROM training WHERE YEAR(startdate) <= 2025");
    $trainingIds = [];
    while ($row = mysqli_fetch_assoc($trainingIdsRes)) {
        $trainingIds[] = (int) $row['id'];
    }
    $trainingIdList = empty($trainingIds) ? '0' : implode(',', $trainingIds);

    $output = array_merge($output, archiveTable($conn, $execute, 'session', "trainingid IN ($trainingIdList)", 'training family'));
    $output = array_merge($output, archiveTable($conn, $execute, 'participation', "trainingid IN ($trainingIdList)", 'training family'));
    $output = array_merge($output, archiveTable($conn, $execute, 'training', "id IN ($trainingIdList)", 'training family'));

    $ojtIdsRes = mysqli_query($conn, "SELECT id FROM ojt WHERE YEAR(startdate) <= 2025");
    $ojtIds = [];
    while ($row = mysqli_fetch_assoc($ojtIdsRes)) {
        $ojtIds[] = (int) $row['id'];
    }
    $ojtIdList = empty($ojtIds) ? '0' : implode(',', $ojtIds);

    $output = array_merge($output, archiveTable($conn, $execute, 'participateojt', "ojtid IN ($ojtIdList)", 'ojt family'));
    $output = array_merge($output, archiveTable($conn, $execute, 'ojt', "id IN ($ojtIdList)", 'ojt family'));

    $output = array_merge($output, archiveTable($conn, $execute, 'tna', "(dateapprove IS NOT NULL AND YEAR(dateapprove) <= 2025) OR (dateapprove IS NULL AND CAST(year AS UNSIGNED) <= 2025)", 'tna'));
    $output = array_merge($output, archiveTable($conn, $execute, 'pme', "YEAR(from_date) <= 2025", 'pme'));
    $output = array_merge($output, archiveTable($conn, $execute, 'tni', "CAST(year AS UNSIGNED) <= 2025", 'tni'));
    $output = array_merge($output, archiveTable($conn, $execute, 'certificate', "upload_date IS NOT NULL AND YEAR(upload_date) <= 2025", 'certificate'));

    return $output;
}

$isConfirmedPost = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes';

if ($isConfirmedPost) {
    $results = runArchive($conn, true);
    ?>
    <!DOCTYPE html>
    <html><head><title>Archive - Executed</title></head>
    <body style="font-family: monospace; white-space: pre-wrap; padding: 20px;">
        <h2>Archive executed</h2>
        <?php echo htmlspecialchars(implode("\n", $results)); ?>
        <p><strong>Done. Please delete this file (archive_2025_and_below_web.php) from the server now.</strong></p>
    </body></html>
    <?php
} else {
    $results = runArchive($conn, false);
    ?>
    <!DOCTYPE html>
    <html><head><title>Archive - Preview</title></head>
    <body style="font-family: monospace; white-space: pre-wrap; padding: 20px;">
        <h2>Dry run preview (nothing has been changed yet)</h2>
        <?php echo htmlspecialchars(implode("\n", $results)); ?>
        <p>Review the counts above. Clicking below will archive matching rows into *_archive tables, then delete them from the live tables.</p>
        <form method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" style="font-size: 16px; padding: 10px 20px;" onclick="return confirm('This will permanently move and delete the listed rows from the live tables. Continue?');">Confirm &amp; Execute</button>
        </form>
    </body></html>
    <?php
}
