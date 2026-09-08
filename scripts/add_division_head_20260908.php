<?php
// One-time schema change: adds divisions.head_user_id, mirroring
// departments.hod_user_id, so a "Head of Division" can be assigned - the
// level above a department's HOD. Needed because the org model currently
// has no way to record anyone above HOD level: divisions only had
// name/shortname, so there was no field to point at whoever a department
// HOD reports to.
//
// Same FK shape as departments.hod_user_id (fk_dept_hod): nullable,
// ON DELETE SET NULL (removing the user un-assigns them as division head
// rather than blocking the delete), ON UPDATE CASCADE.
//
// Safety:
//   - Dry run by default: reports whether the column/FK already exist and
//     what would be added. Pass --apply to actually run the ALTER TABLE.
//   - Purely additive (ADD COLUMN ... NULL) - no existing data is touched,
//     nothing to back up or undo beyond DROP FOREIGN KEY / DROP COLUMN.
//
// USAGE
//  - CLI (php scripts/add_division_head_20260908.php [--apply]) - no token
//    needed, CLI access already implies server access.
//  - HTTP GET with a matching ?key=, for hosts with no terminal/SSH access -
//    upload this file normally then visit it in a browser:
//      https://<your-domain>/ldms/scripts/add_division_head_20260908.php?key=f0ad68605fe3d13051d72809cc9403544c5d670015e42d6a
//      https://<your-domain>/ldms/scripts/add_division_head_20260908.php?key=...&apply=1
//    Change SCHEMA_DEPLOY_TOKEN below before relying on this - the value
//    here is a placeholder generated for setup, not a secret kept out of
//    source control. Delete this file from the server once applied - it's
//    a one-time migration, not something that needs to stay reachable.
define('SCHEMA_DEPLOY_TOKEN', 'f0ad68605fe3d13051d72809cc9403544c5d670015e42d6a');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(SCHEMA_DEPLOY_TOKEN, (string) $_GET['key'])) {
        http_response_code(403);
        header('Content-Type: text/plain');
        die('Forbidden');
    }
    header('Content-Type: text/plain');
}

require __DIR__ . '/../dbconn.php';

$apply = (PHP_SAPI === 'cli')
    ? in_array('--apply', $argv, true)
    : (isset($_GET['apply']) && $_GET['apply'] === '1');

echo "==============================================================\n";
echo "Add divisions.head_user_id - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

$colExists = false;
$res = $conn->query("SHOW COLUMNS FROM divisions LIKE 'head_user_id'");
if ($res && $res->num_rows > 0) {
    $colExists = true;
}
echo $colExists
    ? "Column divisions.head_user_id already exists - nothing to do.\n"
    : "Column divisions.head_user_id is missing.\n";

if (!$colExists) {
    if ($apply) {
        $ok1 = $conn->query(
            "ALTER TABLE divisions ADD COLUMN head_user_id INT NULL AFTER shortname"
        );
        if (!$ok1) {
            echo "FAILED to add column: {$conn->error}\n";
            exit(1);
        }
        echo "Column added.\n";

        $ok2 = $conn->query(
            "ALTER TABLE divisions
             ADD CONSTRAINT fk_division_head FOREIGN KEY (head_user_id)
             REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE"
        );
        if (!$ok2) {
            echo "FAILED to add FK constraint: {$conn->error}\n";
            exit(1);
        }
        echo "Foreign key fk_division_head added.\n";
    } else {
        echo "Would run:\n";
        echo "  ALTER TABLE divisions ADD COLUMN head_user_id INT NULL AFTER shortname;\n";
        echo "  ALTER TABLE divisions ADD CONSTRAINT fk_division_head FOREIGN KEY (head_user_id)\n";
        echo "    REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE;\n";
        echo "Re-run with --apply to commit.\n";
    }
}

echo "\n==============================================================\n";
echo $apply ? "Done.\n" : "Dry run complete - nothing written. Re-run with --apply to commit.\n";
echo "==============================================================\n";
?>
