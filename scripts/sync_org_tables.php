<?php
// Usage: php scripts/sync_org_tables.php
require_once __DIR__ . '/../dbconn.php';
require_once __DIR__ . '/../division_department_section.php';

// use the mysqli connection from dbconn.php
// ensure $conn is a mysqli connection
if (!($conn instanceof mysqli)) {
    die('dbconn.php must expose a mysqli $conn object for this script.');
}

function upsertDivision($mysqli, $label) {
    $shortname = getDivisionShortName($label);
    $stmt = $mysqli->prepare('SELECT id FROM divisions WHERE name = ?');
    $stmt->bind_param('s', $label);
    $stmt->execute();
    $id = null;
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        if ($shortname !== null) {
            $stmt = $mysqli->prepare('UPDATE divisions SET shortname = ? WHERE id = ?');
            $stmt->bind_param('si', $shortname, $id);
            $stmt->execute();
            $stmt->close();
        }
        return $id;
    }
    $stmt->close();
    $stmt = $mysqli->prepare('INSERT INTO divisions (name, shortname) VALUES (?, ?)');
    $stmt->bind_param('ss', $label, $shortname);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    return $newId;
}

function upsertDepartment($mysqli, $divisionId, $label) {
    $shortname = getDepartmentShortName($label);
    $stmt = $mysqli->prepare('SELECT id FROM departments WHERE division_id = ? AND name = ?');
    $stmt->bind_param('is', $divisionId, $label);
    $stmt->execute();
    $id = null;
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        if ($shortname !== null) {
            $stmt = $mysqli->prepare('UPDATE departments SET shortname = ? WHERE id = ?');
            $stmt->bind_param('si', $shortname, $id);
            $stmt->execute();
            $stmt->close();
        }
        return $id;
    }
    $stmt->close();
    $stmt = $mysqli->prepare('INSERT INTO departments (division_id, name, shortname) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $divisionId, $label, $shortname);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    return $newId;
}

function upsertSection($mysqli, $departmentId, $label) {
    $shortname = getSectionShortName($label);
    $stmt = $mysqli->prepare('SELECT id FROM sections WHERE department_id = ? AND name = ?');
    $stmt->bind_param('is', $departmentId, $label);
    $stmt->execute();
    $id = null;
    $stmt->bind_result($id);
    if ($stmt->fetch()) {
        $stmt->close();
        if ($shortname !== null) {
            $stmt = $mysqli->prepare('UPDATE sections SET shortname = ? WHERE id = ?');
            $stmt->bind_param('si', $shortname, $id);
            $stmt->execute();
            $stmt->close();
        }
        return $id;
    }
    $stmt->close();
    $stmt = $mysqli->prepare('INSERT INTO sections (department_id, name, shortname) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $departmentId, $label, $shortname);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    return $newId;
}

$map = getOrgStructure();

foreach ($map as $divisionLabel => $departments) {
    $divisionId = upsertDivision($conn, $divisionLabel);
    foreach ($departments as $departmentLabel => $sections) {
        $departmentId = upsertDepartment($conn, $divisionId, $departmentLabel);
        foreach ($sections as $sectionLabel) {
            upsertSection($conn, $departmentId, $sectionLabel);
        }
    }
}

// Update user table IDs according to existing text values
// use explicit COLLATE to avoid illegal mix of collations between database columns
$update = $conn->prepare("UPDATE user u
 LEFT JOIN divisions d ON u.division COLLATE utf8mb4_general_ci = d.name COLLATE utf8mb4_general_ci
 LEFT JOIN departments dp ON u.department COLLATE utf8mb4_general_ci = dp.name COLLATE utf8mb4_general_ci AND dp.division_id = d.id
 LEFT JOIN sections s ON u.section COLLATE utf8mb4_general_ci = s.name COLLATE utf8mb4_general_ci AND s.department_id = dp.id
 SET u.division_id = d.id,
    u.department_id = dp.id,
    u.section_id = CASE WHEN u.usertype = 'HOD' THEN NULL ELSE s.id END,
    u.section = CASE WHEN u.usertype = 'HOD' THEN '' ELSE u.section END");
if ($update) {
    $ok = $update->execute();
    if ($ok && $update->errno === 0) {
        echo "User references updated successfully.\n";
    } else {
        echo "Error updating user references: " . $update->error . "\n";
    }
    $update->close();
} else {
    echo "Prepare failed: " . $conn->error . "\n";
}

echo "Done.\n";
