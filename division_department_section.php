<?php
// Centralized organization structure map for division, department, and section.
// Edit this file only when division/department/section names change.

$ORG_STRUCTURE = [
    "BUSINESS DEVELOPMENT & STRATEGY" => [
        "BUSINESS DEVELOPMENT" => [
            "-",
            "COST PLANNING & COMMERCIAL",
            "MARKETING & SALES",
            "CRM AND BUSINESS LIAISON"
        ],
        "PROGRAM MANAGEMENT 1" => [
            "-",
            "AEROSPACE",
            "PRO. MGMT. OTHERS",
            "PRO. MGMT. PERODUA"
        ],
        "PROGRAM MANAGEMENT 2" => [
            "-",
            "PRO. MGMT. HONDA",
            "PRO. MGMT NON AUTO"
        ],
        "PROGRAM MANAGEMENT 3" => [
            "-",
            "PROGRAM MANAGEMENT PROTON",
            "QUALITY DEVELOPMENT PROTON"
        ],
        "COSTING & COMMERCIAL" => ["-"]
    ],
    "DHMSB OPERATIONS" => [
        "OPERATION IV" => ["-", "COSTING", "PMO & PROCESS", "EV CHARGER"],
        "MANUFACTURING & SCM (DHMSB)" => ["-", "FABRICATION", "MFG - USJ", "QUALITY MANAGEMENT"],
        "HICOM INTELLIGENT MOBILITY" => ["-"]
    ],
    "FINANCE" => [
        "IT & DIGITALISATION" => ["-", "APPLICATION", "PROJECT & DEVELOPMENT", "SYSTEM ADMINISTRATION"],
        "FINANCE" => ["-", "INVENTORY FIXED ASSET MANAGEMENT", "REPORTING TAXATION", "ACCOUNT PAYABLE", "ACCOUNT RECEIVABLE", "TREASURY & SALES"],
        "PROCUREMENT & VENDOR DEVELOPMENT" => ["-", "GENERAL PURCHASES", "PROCUREMENT & VENDOR DEVELOPMENT", "RAW MATERIALS & COMPONENTS", "VENDOR MANAGEMENT & DEVELOPMENT"]
    ],
    "HUMAN CAPITAL & ESG" => [
        "CULTURE & TALENT MANAGEMENT" => ["-", "CULTURE SECTION", "LEARNING & DEVELOPMENT", "RECRUITMENT"],
        "REWARDS & ADMIN" => ["-", "ADMINISTRATION", "COMPENSATION & BENEFIT"],
        "ESG, HEALTH AND SAFETY" => ["-", "SHE"]
    ],
    "OPERATION MANAGEMENT" => [
        "MANUFACTURING & SCM BB/RASA" => ["-", "MFG - BUKIT BERUNTUNG", "SUPPLY CHANGE MANAGEMENT BB"],
        "MANUFACTURING & SCM PEGOH" => ["-", "ASSEMBLY - PEGOH", "SUPPLY CHAIN MANAGEMENT PEGOH"],
        "MANUFACTURING & SCM PEKAN" => ["-", "PROJECT MMCMM"],
        "MANUFACTURING & SCM SA1" => ["-", "ASSEMBLY", "STAMPING", "SUPPLY CHAIN MANAGEMENT SA1"],
        "MANUFACTURING & SCM SA2" => ["-", "ASSEMBLY - SA 2", "PAINTING", "STAMPING- SA 2", "WELDING", "LOGISTIC", "PROGRESS CONTROL- SA 2", "RECEIVING- SA 2"],
        "MANUFACTURING & SCM TM1 (FIF)" => ["-", "ASSEMBLY - TGM 1 (FIF)", "SUPPLY CHAIN MGMT- TGM 1 (FIF)"],
        "MANUFACTURING & SCM TM2 (OSI)" => ["-", "ASSEMBLY - TGM 2", "SUPPLY CHAIN MANAGEMENT - TGM 2"],
        "INVENTORY MANAGEMENT PLANNING (IMP)" => ["-", "INVENTORY MANAGEMENT SYSTEM BB", "INVENTORY MANAGEMENT SYSTEM SA2", "INVENTORY MANAGEMENT SYSTEM PEGOH", "INVENTORY MANAGEMENT SYSTEM (FIF)", "INVENTORY MANAGEMENT SYSTEM SA1", "COST & EXPENDITURE"]
    ],
    "ENGINEERING AND R&D" => [
        "ENGINEERING MANAGEMENT 1" => ["-"],
        "ENGINEERING MANAGEMENT 2" => ["-"],
        "ENERGY & FACILITY MANAGEMENT" => ["-", "FACILITY & PLANT MAINTENANCE"],
        "EQUIPMENT MAINTENANCE 1 (SA1, BB/RASA & PGH)" => ["-", "PROJECT", "EQUIPMENT MNTC ASSEMBLY- FIF", "EQUIPMENT MNTC ASSEMBLY- PHN 1", "EQUIPMENT MNTC ASSEMBLY- TGM", "EQUIPMENT MNTC STAMPING- SA1"],
        "EQUIPMENT MAINTENANCE 2 (SA2, DHMSB, TM1 & TM2)" => ["-", "EQUIPMENT MNTC ASSEMBLY-TGM", "EQUIPMENT MNTC ASSEMBLY- FIF", "EQUIPMENT MNTC ASSEMBLY- PHN 2"],
        "PROCESS ENGINEERING" => ["-", "INDUSTRIAL ENGINEERING", "PROCESS DEVELOPMENT", "PROCESS IMPROVEMENT"],
        "RESEARCH & DEVELOPMENT" => ["-", "MFG. PROCESS PLANNING", "COMPUTER AIDED ENGINEERING", "DESIGN"],
        "TOOLING DESIGN & DEVELOPMENT" => ["-", "TOOLING FABRICATION", "TOOLING DESIGN", "BIW PROJECT MANAGEMENT", "JIG MAKING", "MFG. PROCESS PLANNING", "TE - DIES MAKING", "TOOLING MAINTENANCE"],
        "SPECIAL PROJECT" => ["-"]
    ],
    "COO OFFICE" => [
        "COO OFFICE" => ["-"]
    ],
    "QUALITY MANAGEMENT" => [
        "QUALITY SYSTEM & BCM" => ["-", "HMS", "QMS", "SUPPLIER QUALITY", "PROCESS IMPROVEMENT"],
        "QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)" => ["-", "QUALITY ASSURANCE BB", "QUALITY ASSURANCE TM1", "QUALITY ASSURANCE TM2", "QUALITY PCC", "QUALITY PERODUA (MFG-BB)", "QUALITY CONTROL - FIF", "QUALITY CONTROL - TM2", "QUALITY CONTROL - BB"],
        "QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)" => ["-", "QUALITY ASSURANCE PEGOH", "QUALITY ASSURANCE PEKAN", "QUALITY CONTROL PEGOH"],
        "QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)" => ["-", "CUSTOMER SERVICE - SA 1", "CUSTOMER QUALITY AND LAB SA 1", "CUSTOMER SERVICES", "QUALITY ASSURANCE - SA 1", "QUALITY ASSURANCE - SA 2", "CUSTOMER QUALITY AND QUALITY ENGINEERING SA1", "CUSTOMER QUALITY LAB- SA2", "QUALITY CONTROL ASSY STPG - SA1", "QUALITY CONTROL - SA 2"],
        "QUALITY DEVELOPMENT" => ["-", "QUALITY DEVELOPMENT PROTON", "QUALITY RESIDENT ENGINEERING OP1", "QUALITY RESIDENT ENGINEERING OP2"]
    ]
];

// Section-to-label mapping for business dashboard category translation.
$SECTION_TO_CATEGORY = [
    "COST PLANNING & COMMERCIAL" => "CP&C",
    "MARKETING & SALES" => "M&S",
    "AEROSPACE" => "AER",
    "PRO. MGMT. OTHERS" => "PMO",
    "PRO. MGMT. PERODUA" => "PMP",
    "PRO. MGMT. HONDA" => "PMH",
    "PRO. MGMT NON AUTO" => "PMNA",
    "QUALITY RESIDENT ENGINEERING OP1" => "QREO1",
    "QUALITY RESIDENT ENGINEERING OP2" => "QREO2"
];

$DEPARTMENT_SHORTNAMES = [
    "BUSINESS DEVELOPMENT" => "BD",
    "IT & DIGITALISATION" => "IT",
    "FINANCE" => "FIN",
    "MANUFACTURING & SCM (DHMSB)" => "M&S(DHMSB)",
    "OPERATION IV" => "OPIV",
    "PROCUREMENT & VENDOR DEVELOPMENT" => "PVD",
    "PROGRAM MANAGEMENT 1" => "PM1",
    "PROGRAM MANAGEMENT 2" => "PM2",
    "PROGRAM MANAGEMENT 3" => "PM3",
    "QUALITY DEVELOPMENT" => "QD",
    "REWARDS & ADMIN" => "R&A",
    "CULTURE & TALENT MANAGEMENT" => "C&TM",
    "MANUFACTURING & SCM PEKAN" => "MFG PKN",
    "MANUFACTURING & SCM BB/RASA" => "MFG BB/RASA",
    "MANUFACTURING & SCM PEGOH" => "MFG PGH",
    "MANUFACTURING & SCM SA1" => "MFG SA1",
    "MANUFACTURING & SCM SA2" => "MFG SA2",
    "INVENTORY MANAGEMENT PLANNING (IMP)" => "IMP",
    "MANUFACTURING & SCM TM1 (FIF)" => "MFG TM1",
    "MANUFACTURING & SCM TM2 (OSI)" => "MFG TM2",
    "COSTING & COMMERCIAL" => "C&C",
    "ESG, HEALTH AND SAFETY" => "ESG",
    "QUALITY ASSURANCE & CONTROL 2 (BB/RASA, TM 1 & TM 2)" => "QA&C2",
    "QUALITY ASSURANCE & CONTROL 3 (PEGOH & PEKAN)" => "QA&C3",
    "QUALITY ASSURANCE & CONTROL 1 (SA1 & SA2)" => "QA&C1",
    "QUALITY SYSTEM & BCM" => "QS",
    "ENGINEERING MANAGEMENT 1" => "ENG 1",
    "ENGINEERING MANAGEMENT 2" => "ENG 2",
    "ENERGY & FACILITY MANAGEMENT" => "EFM",
    "EQUIPMENT MAINTENANCE 1 (SA1, BB/RASA & PGH)" => "EQM1",
    "EQUIPMENT MAINTENANCE 2 (SA2, DHMSB, TM1 & TM2)" => "EQM2",
    "PROCESS ENGINEERING" => "PE",
    "RESEARCH & DEVELOPMENT" => "R&D",
    "TOOLING DESIGN & DEVELOPMENT" => "TE",
    "COO OFFICE" => "COO",
    "HICOM INTELLIGENT MOBILITY" => "HIM"
];

$DIVISION_SHORTNAMES = [
    "BUSINESS DEVELOPMENT & STRATEGY" => "BDS",
    "DHMSB OPERATIONS" => "DHMSB",
    "FINANCE" => "FIN",
    "HUMAN CAPITAL & ESG" => "HCESG",
    "OPERATION MANAGEMENT" => "OPS",
    "ENGINEERING AND R&D" => "ERD",
    "COO OFFICE" => "COO",
    "QUALITY MANAGEMENT" => "QM"
];

function getOrgStructure()
{
    global $ORG_STRUCTURE;
    return $ORG_STRUCTURE;
}

function getDivisions()
{
    return array_keys(getOrgStructure());
}

function getDepartments($division)
{
    $org = getOrgStructure();
    return isset($org[$division]) ? array_keys($org[$division]) : [];
}

function getSections($division, $department)
{
    $org = getOrgStructure();
    if (isset($org[$division][$department])) {
        return $org[$division][$department];
    }
    return [];
}

function getSectionCategory($section)
{
    global $SECTION_TO_CATEGORY;
    return isset($SECTION_TO_CATEGORY[$section]) ? $SECTION_TO_CATEGORY[$section] : '-';
}

function buildAutoShortName($label)
{
    $label = trim((string) $label);
    if ($label === '') {
        return null;
    }

    if ($label === '-') {
        return '-';
    }

    $upper = strtoupper($label);
    $tokens = preg_split('/[^A-Z0-9]+/', $upper, -1, PREG_SPLIT_NO_EMPTY);
    if (!$tokens) {
        return null;
    }

    $stopWords = ['AND', 'OF', 'THE'];
    $parts = [];

    foreach ($tokens as $token) {
        if (in_array($token, $stopWords, true)) {
            continue;
        }

        if (preg_match('/\d/', $token) || strlen($token) <= 3) {
            $parts[] = $token;
        } else {
            $parts[] = substr($token, 0, 1);
        }
    }

    if (!$parts) {
        $parts = [$tokens[0]];
    }

    $shortname = implode('', $parts);
    if (strlen($shortname) > 10) {
        $shortname = substr($shortname, 0, 10);
    }

    return $shortname;
}

function getDepartmentShortName($department)
{
    global $DEPARTMENT_SHORTNAMES;
    if (isset($DEPARTMENT_SHORTNAMES[$department])) {
        return $DEPARTMENT_SHORTNAMES[$department];
    }
    return buildAutoShortName($department);
}

function getDivisionShortName($division)
{
    global $DIVISION_SHORTNAMES;
    if (isset($DIVISION_SHORTNAMES[$division])) {
        return $DIVISION_SHORTNAMES[$division];
    }
    return buildAutoShortName($division);
}

function getSectionShortName($section)
{
    $sectionCategory = getSectionCategory($section);
    if ($sectionCategory !== '-') {
        return $sectionCategory;
    }
    return buildAutoShortName($section);
}

function getDepartmentBySection($section)
{
    $org = getOrgStructure();
    foreach ($org as $division => $departments) {
        foreach ($departments as $department => $sections) {
            if (in_array($section, $sections, true)) {
                return $department;
            }
        }
    }
    return null;
}

function getDivisionByDepartment($department)
{
    $org = getOrgStructure();
    foreach ($org as $division => $departments) {
        if (isset($departments[$department])) {
            return $division;
        }
    }
    return null;
}

/**
 * New helpers for normalized tables
 */
function getDivisionsList()
{
    global $conn; // make sure dbconn exposes $conn in scripts that include this file
    if (!isset($conn)) {
        return [];
    }
    $result = mysqli_query($conn, "SELECT id, name FROM divisions ORDER BY name");
    $out = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $out[$row['id']] = $row['name'];
    }
    return $out;
}

function getDepartmentsByDivisionId($divisionId)
{
    global $conn;
    if (!isset($conn)) {
        return [];
    }
    $stmt = mysqli_prepare($conn, "SELECT id, name FROM departments WHERE division_id = ? ORDER BY name");
    mysqli_stmt_bind_param($stmt, 'i', $divisionId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $out = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $out[$row['id']] = $row['name'];
    }
    mysqli_stmt_close($stmt);
    return $out;
}

function getSectionsByDepartmentId($departmentId)
{
    global $conn;
    if (!isset($conn)) {
        return [];
    }
    $stmt = mysqli_prepare($conn, "SELECT id, name FROM sections WHERE department_id = ? ORDER BY name");
    mysqli_stmt_bind_param($stmt, 'i', $departmentId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $out = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $out[$row['id']] = $row['name'];
    }
    mysqli_stmt_close($stmt);
    return $out;
}

function getDivisionIdByName($name)
{
    global $conn;
    if (!isset($conn)) {
        return null;
    }
    $stmt = mysqli_prepare($conn, "SELECT id FROM divisions WHERE name = ?");
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? $row['id'] : null;
}

function getDepartmentIdByName($divisionId, $name)
{
    global $conn;
    if (!isset($conn)) {
        return null;
    }
    $stmt = mysqli_prepare($conn, "SELECT id FROM departments WHERE division_id = ? AND name = ?");
    mysqli_stmt_bind_param($stmt, 'is', $divisionId, $name);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? $row['id'] : null;
}

function getSectionIdByName($departmentId, $name)
{
    global $conn;
    if (!isset($conn)) {
        return null;
    }
    $stmt = mysqli_prepare($conn, "SELECT id FROM sections WHERE department_id = ? AND name = ?");
    mysqli_stmt_bind_param($stmt, 'is', $departmentId, $name);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? $row['id'] : null;
}

function getDbOrgStructure()
{
    global $conn;

    if (!isset($conn)) {
        return [];
    }

    $divisions = [];
    $divisionResult = mysqli_query($conn, "SELECT id, name FROM divisions ORDER BY name");
    if (!$divisionResult) {
        return [];
    }

    while ($divisionRow = mysqli_fetch_assoc($divisionResult)) {
        $divisions[(int) $divisionRow['id']] = $divisionRow['name'];
    }

    $structure = [];
    foreach ($divisions as $divisionName) {
        $structure[$divisionName] = [];
    }

    $sql = "SELECT d.name AS division_name, dp.name AS department_name, s.name AS section_name
            FROM divisions d
            LEFT JOIN departments dp ON dp.division_id = d.id
            LEFT JOIN sections s ON s.department_id = dp.id
            ORDER BY d.name, dp.name, s.name";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return [];
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $divisionName = $row['division_name'];
        $departmentName = $row['department_name'];
        $sectionName = $row['section_name'];

        if ($departmentName === null || $departmentName === '') {
            continue;
        }

        if (!isset($structure[$divisionName][$departmentName])) {
            $structure[$divisionName][$departmentName] = [];
        }

        if ($sectionName !== null && $sectionName !== '') {
            $structure[$divisionName][$departmentName][] = $sectionName;
        }
    }

    return $structure;
}

