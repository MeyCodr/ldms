<?php
// One-shot deploy script for moving the TNA "Training Required" dropdown
// options out of hardcoded HTML and into the database (2026-10-02).
//
// The 7 TNA form pages (admin/clerk/staff-hod manage_tna.php + manage_grade.php,
// staff/office/tna/tna.php) now read their options through
// tna_training_options.php, which needs these two tables to exist. Uploading
// the changed .php files without running this first leaves every
// "Training Required" dropdown broken.
//
//   1. TABLES: tna_training_category (option groups, e.g. the Functional
//      Awareness optgroups) and tna_training_option (one row per option).
//   2. SEED: the exact lists that were hardcoded in the forms on 2026-10-02.
//      OTHERS is not stored - the forms always add it themselves. One
//      duplicate (OSH COORDINATOR TRAINING appeared twice in Functional ->
//      Industrial Safety) is seeded once.
//
// Values already saved in tna.training that are not in these lists need no
// seeding: tna_training_options.php emits them as hidden options so existing
// TNAs keep their value. Step 3 just reports them so you know they exist.
//
// SAFETY
//   - Dry run by default. Pass --apply to actually create and seed.
//   - Idempotent: tables are created only if missing, and the seed only runs
//     while tna_training_option is still empty, so re-running never
//     duplicates options or overwrites changes an admin has made since.
//
// USAGE
//  - CLI: php scripts/deploy_tna_training_options_20261002.php [--apply]
//  - HTTP (cPanel, no SSH): upload, then visit
//      https://<your-domain>/ldms/scripts/deploy_tna_training_options_20261002.php?key=7c612f784574757287b428fa777a60fb3c4f3d7ccd4b5645
//      ...&apply=1 to apply.
//    Change DEPLOY_TNA_OPTIONS_TOKEN before relying on this - the value here
//    is a placeholder generated for setup, not a secret kept out of source
//    control. Delete this file from the server once you're done with it.
define('DEPLOY_TNA_OPTIONS_TOKEN', '7c612f784574757287b428fa777a60fb3c4f3d7ccd4b5645');

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['key']) || !hash_equals(DEPLOY_TNA_OPTIONS_TOKEN, (string) $_GET['key'])) {
        http_response_code(403);
        header('Content-Type: text/plain');
        die('Forbidden');
    }
    header('Content-Type: text/plain');
    @set_time_limit(0);
}

require __DIR__ . '/../dbconn.php';

$apply = (PHP_SAPI === 'cli')
    ? in_array('--apply', $argv, true)
    : (isset($_GET['apply']) && $_GET['apply'] === '1');

// section => [group name ('' = no group) => [option names in order]]
$seed = [
    'esgaware' => [
        '' => [
            'CARBON BORDER ADJUSTMENT MECHANISM (CBAM) COMPLIANCE',
            'EU BATTERY REGULATION AWARENESS',
            'ESG REPORTING STANDARDS (BURSA, SC)',
            'LIFE CYCLE ASSESSMENT (LCA) FOR AUTOMOTIVE',
            'WASTE REDUCTION & CIRCULAR ECONOMY',
            'ESG AWARENESS AND STRATEGIC IMPLEMENTATION',
            'ENVIRONMENTAL MANAGEMENT SYSTEM (ISO 14001) AND GOVERNMENT COMPLIANCE',
            'GOVERNMENT INCENTIVES FOR SUSTAINABLE MANUFACTURING',
            'GREEN BOOK',
        ],
    ],
    'selfaware' => [
        '' => [
            'TRAIN THE TRAINER (TTT)',
            'EFFECTIVE BUSINESS COMMUNICATION',
            'ENGLISH FOR PROFESSIONAL PURPOSES',
            'INTERPERSONAL COMMUNICATION AND RELATIONSHIP BUILDING',
            'PRESENTATION AND PUBLIC SPEAKING SKILLS',
            'EMOTIONAL INTELLIGENCE (EQ) FOR WORKPLACE EFFECTIVENESS',
            'CONFLICT RESOLUTION AND NEGOTIATION SKILLS',
            'TIME MANAGEMENT AND PRODUCTIVITY SKILLS',
            'CROSS-CULTURAL COMMUNICATION',
            'CUSTOMER SERVICE AND PROFESSIONAL ETIQUETTE',
            'CREATIVE THINKING AND PROBLEM-SOLVING',
            'MICROSOFT TEAMS AND COLLABORATION TOOLS',
            'TIME MANAGEMENT USING MICROSOFT 365 TOOLS',
            'GREEN TECHNOLOGY AND ENERGY EFFICIENCY',
            'ANTI BRRIBERY & ANTI CORRUPTION',
        ],
    ],
    'leadaware' => [
        '' => [
            'ETHICAL LEADERSHIP & GOVERNANCE',
            'RESILIENCE & STRESS MANAGEMENT',
            'INCLUSIVE LEADERSHIP & DIVERSITY AWARENESS',
            'HIGH IMPACT TRANSFORMATIONAL LEADERSHIP SKILLS FOR MANAGERS & LEADERS',
            'GREEN TECHNOLOGY AND ENERGY EFFICIENCY',
            'LEADING HIGH-PERFORMANCE TEAMS IN MANUFACTURING',
            'STRATEGIC LEADERSHIP AND DECISION-MAKING',
            'CHANGE MANAGEMENT AND CULTURE TRANSFORMATION',
            'CUSTOMER-CENTRIC LEADERSHIP',
            'STRATEGIC SUPPLY CHAIN LEADERSHIP',
            'CROSS-FUNCTIONAL COLLABORATION AND INFLUENCE',
        ],
    ],
    'dataaware' => [
        '' => [
            'DATA PRIVACY & PDPA 2010 IN MANUFACTURING',
            'AI & ROBOTICS IN AUTOMOTIVE',
            'EV FUNDAMENTALS (BATTERY, CHARGER, REGULATIONS)',
            'MICROSOFT POWER BI: DATA VISUALISATION AND DASHBOARD CREATION',
            'MICROSOFT EXCEL: ADVANCED FORMULAS, PIVOT TABLES, AND MACROS',
            'MICROSOFT VISUAL BASIC FOR APPLICATIONS (VBA) AUTOMATION',
            'MICROSOFT POWER AUTOMATE: WORKFLOW AUTOMATION',
        ],
    ],
    'functional' => [
        'IT, TECHNICAL & MAINTENANCE' => [
            'PREDICTIVE MAINTENANCE USING IOT',
            'EV COMPONENT SAFETY STANDARDS',
            'COST REDUCTION TECHNIQUES FOR MAINTENANCE',
            'IP & PATENT',
            'KARAKURI',
            'CATIA',
            'CHATGPT FOR PRODUCTIVITY AND TASK AUTOMATION',
            'MAINTENANCE MANAGEMENT AND RELIABILITY ENGINEERING',
            'CNC AND STAMPING MACHINE OPERATION & TROUBLESHOOTING',
            'ELECTRICAL AND MECHANICAL SYSTEMS MAINTENANCE',
            'EV CHARGER TECHNOLOGY AND BATTERY MAINTENANCE',
            'PLC PROGRAMMING AND AUTOMATION CONTROL',
            'IT INFRASTRUCTURE AND NETWORK MANAGEMENT',
            'CYBERSECURITY AND DATA PROTECTION',
            'TROUBLESHOOTING AND ROOT CAUSE ANALYSIS FOR EQUIPMENT FAILURES',
            'INDUSTRIAL ROBOTICS AND AUTOMATED ASSEMBLY SYSTEMS',
            'GOVERNMENT REGULATIONS AND INDUSTRIAL STANDARDS FOR TECHNICAL OPERATIONS',
            'MACHINE LEARNING FOR PROCESS OPTIMISATION',
            'INDUSTRIAL ROBOT SAFETY AND COMPLIANCE',
        ],
        'MANUFACTURING / OPERATION' => [
            'MACHINE SAFETY & LOCKOUT–TAGOUT (LOTO)',
            'ERGONOMICS & MANUAL HANDLING IN PRODUCTION',
            'STAMPING PRESS OPERATION & MAINTENANCE',
            'DIE MAINTENANCE & TROUBLESHOOTING FOR STAMPING & ROLL FORMING',
            'ROLL FORMING TECHNOLOGY & DEFECT PREVENTION',
            'WELDING & ASSEMBLY TECHNIQUES FOR AUTOMOTIVE CHASSIS',
            'ROBOTIC WELDING & AUTOMATION IN ASSEMBLY',
            'STAMPING DEFECTS & TROUBLESHOOTING TECHNIQUES',
            'ASSEMBLY LINE BALANCING & PROCESS OPTIMISATION',
            'FUNDAMENTALS OF ELECTRODEPOSITION',
            'MAINTENANCE OF ED TANKS, RECTIFIERS, FILTERS, AND ULTRAFILTRATION SYSTEMS',
            'ROBOTICS IN MATERIAL HANDLING AND PAINTING SYSTEMS',
            '5S TRAINING',
        ],
        'QUALITY SYSTEMS & PRODUCTIVITY IMPROVEMENT' => [
            'ISO 50001:2018 ENERGY MANAGEMENT SYSTEM',
            'IATF 16949:2024 TRANSITION TRAINING',
            'ISO 37301 COMPLIANCE MANAGEMENT',
            'INTERNAL AUDIT AND CONTROLS',
            'BUSINESS CONTINUITY AND CRISIS MANAGEMENT',
            'RISK MANAGEMENT FRAMEWORKS AND BEST PRACTICES',
            'KAIZEN: CREATE A CULTURE OF CONTINUOUS IMPROVEMENT',
            'LEAN PRODUCTION SYSTEM TRAINING',
            'POKA YOKE - WHAT YOU NEED TO KNOW',
            'SIX SIGMA TOOLS FOR IMPROVEMENT',
            '8D PROBLEM SOLVING',
            'TESTING & LABORATORY MANAGEMENT',
            'STATISTICAL PROCESS CONTROL (SPC) AND DATA-DRIVEN QUALITY IMPROVEMENT',
            'PRODUCTIVITY IMPROVEMENT AND OPERATIONAL EXCELLENCE',
            'SUPPLIER QUALITY MANAGEMENT AND AUDIT',
            'THE 7 NEW QC MANAGEMENT TOOLS',
            'ISO 14001:2015 ENVIRONMENTAL MANAGEMENT SYSTEM (EMS) LEAD AUDITING TRAINING',
            'ISO 9001:2015 QUALITY MANAGEMENT SYSTEM (QMS) INTERNAL AUDITOR TRAINING',
            'LEAD AUDITOR IATF',
            'ISO 45001:2018 OCCUPATIONAL HEALTH AND SAFETY REQUIREMENTS AND INTERNAL AUDITING',
        ],
        'INDUSTRIAL SAFETY' => [
            'HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE',
            'OCCUPATIONAL HEALTH & SAFETY (OHS) AWARENESS',
            'INDUSTRIAL MACHINE SAFETY AND LOCKOUT/TAGOUT (LOTO) PROCEDURES',
            'HAZARD IDENTIFICATION AND RISK ASSESSMENT (HIRA)',
            'FIRE SAFETY AND EMERGENCY RESPONSE',
            'ERGONOMICS AND MANUAL HANDLING SAFETY',
            'HSE MANAGEMENT SYSTEM AND ISO 45001 COMPLIANCE',
            'CHEMICAL SAFETY AND HAZARDOUS MATERIAL HANDLING',
            'SAFETY LEADERSHIP AND CULTURE BUILDING',
            'INCIDENT INVESTIGATION AND REPORTING',
            'EV CHARGER AND ELECTRICAL SAFETY',
            'OVERHEAD CRANE',
            'FORKLIFT',
            'OSH COORDINATOR TRAINING',
            'WORKING AT HEIGHT',
            'SAFETY AWARENESS',
            'SCHEDULED WASTE MANAGEMENT',
        ],
        'HUMAN CAPITAL' => [
            'SUCCESSION PLANNING',
            'ANTI-FORCED LABOUR COMPLIANCE (ILO + US CBP IMPORT BAN)',
            'HRD CORP 2026 CLAIMABLE TRAINING RULES',
            'TALENT ACQUISITION AND RECRUITMENT STRATEGIES',
            'PERFORMANCE MANAGEMENT SYSTEMS AND KPI TRACKING',
            'EMPLOYEE ENGAGEMENT AND RETENTION STRATEGIES',
            'LEARNING & DEVELOPMENT PLANNING',
            'COMPENSATION, BENEFITS, AND PAYROLL MANAGEMENT',
            'LABOUR LAW AND EMPLOYMENT COMPLIANCE',
            'HR ANALYTICS AND PEOPLE DATA MANAGEMENT',
            'COACHING AND MENTORING SKILLS FOR MANAGERS',
            'CHANGE MANAGEMENT AND CULTURE TRANSFORMATION',
            'DIVERSITY, EQUITY, AND INCLUSION (DEI) IN MANUFACTURING',
            'EMPLOYEE WELLNESS AND WORK-LIFE BALANCE PROGRAMS',
        ],
        'LEGAL & GOVERNANCE' => [
            'CONTRACT AND LEGAL COMPLIANCE MANAGEMENT',
            'CORPORATE AND COMMERCIAL LAW AWARENESS',
            'REGULATORY COMPLIANCE IN AUTOMOTIVE INDUSTRY',
            'DISPUTE RESOLUTION AND LEGAL RISK MANAGEMENT',
            'INTELLECTUAL PROPERTY ENFORCEMENT AND INFRINGEMENT MANAGEMENT',
        ],
        'SALES, MARKETING & CUSTOMER SERVICE' => [
            'B2B SALES STRATEGIES FOR MANUFACTURING',
            'INDUSTRIAL MARKETING AND BRAND POSITIONING',
            'CUSTOMER RELATIONSHIP MANAGEMENT (CRM)',
            'TECHNICAL PRODUCT PRESENTATION SKILLS',
            'NEGOTIATION AND CLOSING TECHNIQUES',
            'MARKET AND COMPETITOR ANALYSIS',
            'SALES FORECASTING AND BUSINESS METRICS',
            'CUSTOMER SERVICE EXCELLENCE FOR INDUSTRIAL CLIENTS',
        ],
        'BUSINESS & MANAGEMENT' => [
            'BUSINESS ACUMEN FOR MANUFACTURING PROFESSIONALS',
            'AUTOMOTIVE INDUSTRY OVERVIEW AND TRENDS',
            'FINANCIAL LITERACY FOR NON-FINANCE MANAGERS',
            'STRATEGIC THINKING AND BUSINESS DECISION-MAKING',
            'CUSTOMER AND MARKET INSIGHT FOR BUSINESS SUCCESS',
            'PROJECT ROI AND BUSINESS IMPACT ANALYSIS',
        ],
        'FINANCIAL MANAGEMENT' => [
            'ESG-LINKED FINANCE & GREEN TAX INCENTIVES',
            'TRANSFER PRICING & TAXATION UPDATES 2026',
            'STRATEGIC COST MANAGEMENT IN AUTOMOTIVE',
            'REGULATORY REPORTING AND AUDIT READINESS',
            'E-INVOICING',
            'FINANCIAL PLANNING AND BUDGETING',
            'FINANCIAL REGULATORY COMPLIANCE AND REPORTING',
            'CASH FLOW MANAGEMENT AND WORKING CAPITAL OPTIMISATION',
            'RISK MANAGEMENT AND INTERNAL CONTROLS',
            'FINANCIAL ANALYSIS AND DECISION-MAKING',
            'TAXATION AND GST/SST COMPLIANCE',
            'PROCUREMENT AND FINANCE COLLABORATION',
            'FINANCIAL SYSTEMS AND ERP UTILISATION',
            'INVESTMENT AND CAPITAL EXPENDITURE (CAPEX) MANAGEMENT',
            'AI INNOVATIONS FOR FINANCIAL PROFESSIONALS',
        ],
        'LOGISTICS / SCM / WAREHOUSE / INVENTORY' => [
            'SUPPLIER ESG COMPLIANCE AUDITS',
            'DANGEROUS GOODS HANDLING IN AUTOMOTIVE LOGISTICS',
            'MITI IMPORT/EXPORT REGULATORY UPDATES',
            'SUPPLY CHAIN MANAGEMENT FUNDAMENTALS',
            'WAREHOUSE MANAGEMENT AND INVENTORY CONTROL',
            'LOGISTICS AND TRANSPORTATION MANAGEMENT',
            'DEMAND FORECASTING AND INVENTORY OPTIMISATION',
            'SUSTAINABLE SUPPLY CHAIN PRACTICES',
        ],
        'PROCUREMENT & VENDOR DEVELOPMENT' => [
            'AUTOMOTIVE INDUSTRY SUPPLY CHAIN STANDARDS',
            'STRATEGIC SOURCING AND SUPPLIER SELECTION',
            'SUPPLIER PERFORMANCE MANAGEMENT (SPM)',
            'CONTRACT MANAGEMENT AND NEGOTIATION FOR PROCUREMENT',
            'COST ANALYSIS AND TOTAL COST OF OWNERSHIP (TCO)',
            'VENDOR DEVELOPMENT AND COLLABORATION',
            'DIGITAL PROCUREMENT TOOLS AND ERP UTILISATION',
            'SUSTAINABLE PROCUREMENT AND ESG PRACTICES',
            'SUPPLIER INNOVATION AND TECHNOLOGY COLLABORATION',
            'ADVANCED PROCUREMENT',
            'DEVELOPING PURCHASING POLICIES, PROCESSES AND SLA\'S',
        ],
        'GOVERNANCE RISK AND COMPLIANCE' => [
            'COMPLIANCE MANAGEMENT IN THE AUTOMOTIVE INDUSTRY',
            'ENVIRONMENTAL, SOCIAL, AND GOVERNANCE (ESG) RISK AND COMPLIANCE',
            'HEALTH, SAFETY & ENVIRONMENT (HSE) COMPLIANCE',
            'REGISTERED ELECTRICAL ENERGY MANAGER (REM)',
        ],
    ],
    'busiaware' => [
        'DIGITAL TRANSFORMATION & INNOVATION' => [
            'INDUSTRY 4.0 SMART MANUFACTURING STRATEGIES',
            'DIGITAL SUPPLY CHAIN OPTIMISATION',
            'DIGITAL TWINS IN MANUFACTURING',
            'ROBOTICS PROCESS AUTOMATION (RPA) FOR INDUSTRIAL PROCESSES',
            'ADVANCED DATA VISUALISATION FOR OPERATIONAL DECISION-MAKING',
            'DIGITAL PRODUCT INNOVATION AND EV TECHNOLOGY',
            'INTERNET OF THINGS AND CONNECTED FACTORY IMPLEMENTATION',
            'AGILE AND LEAN DIGITAL PROJECT MANAGEMENT',
            'PREDICTIVE ANALYTICS AND MACHINE LEARNING FOR MANUFACTURING',
            'CLOUD COMPUTING AND ENTERPRISE DIGITAL TOOLS',
            'THE MODERN WORKPLACE',
        ],
    ],
];

echo "==============================================================\n";
echo "TNA training options deploy - " . ($apply ? "APPLYING" : "DRY RUN") . "\n";
echo "==============================================================\n\n";

function table_exists($conn, $name)
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($n);
    $stmt->fetch();
    $stmt->close();
    return $n > 0;
}

// ===== STEP 1: tables =====
echo "--- Step 1: tables ---\n";
$ddl = [
    'tna_training_category' => "CREATE TABLE tna_training_category (
        id INT NOT NULL AUTO_INCREMENT,
        section VARCHAR(100) NOT NULL,
        name VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY idx_section (section, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    'tna_training_option' => "CREATE TABLE tna_training_option (
        id INT NOT NULL AUTO_INCREMENT,
        section VARCHAR(100) NOT NULL,
        category_id INT NULL,
        name VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        updated_by VARCHAR(255) NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_section (section, sort_order),
        KEY idx_category (category_id),
        CONSTRAINT fk_tna_training_option_category FOREIGN KEY (category_id)
            REFERENCES tna_training_category (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
];
foreach ($ddl as $table => $sql) {
    if (table_exists($conn, $table)) {
        echo "  $table: already exists, skipped\n";
    } elseif ($apply) {
        if (!$conn->query($sql)) die("  $table: CREATE FAILED - {$conn->error}\n");
        echo "  $table: created\n";
    } else {
        echo "  $table: would be created\n";
    }
}

// ===== STEP 2: seed =====
echo "\n--- Step 2: seed options ---\n";
$existing = table_exists($conn, 'tna_training_option')
    ? (int) $conn->query("SELECT COUNT(*) FROM tna_training_option")->fetch_row()[0]
    : 0;
$total = 0;
foreach ($seed as $sec => $groups) {
    $n = 0;
    foreach ($groups as $names) $n += count($names);
    $g = count(array_filter(array_keys($groups), 'strlen'));
    echo sprintf("  %-11s %3d options%s\n", $sec, $n, $g ? " in $g group(s)" : '');
    $total += $n;
}
echo "  total: $total options\n";

if ($existing > 0) {
    echo "  tna_training_option already has $existing row(s) - seed skipped\n";
} elseif ($apply) {
    $conn->begin_transaction();
    $catStmt = $conn->prepare("INSERT INTO tna_training_category (section, name, sort_order) VALUES (?, ?, ?)");
    $optStmt = $conn->prepare("INSERT INTO tna_training_option (section, category_id, name, sort_order, updated_by) VALUES (?, ?, ?, ?, 'deploy 20261002')");
    foreach ($seed as $sec => $groups) {
        $catOrder = 0;
        $optOrder = 0;
        foreach ($groups as $cname => $names) {
            $cid = null;
            if ($cname !== '') {
                $catOrder += 10;
                $catStmt->bind_param('ssi', $sec, $cname, $catOrder);
                if (!$catStmt->execute()) { $conn->rollback(); die("  category $sec/$cname FAILED - {$conn->error}\n"); }
                $cid = $conn->insert_id;
            }
            foreach ($names as $name) {
                $optOrder += 10;
                $optStmt->bind_param('sisi', $sec, $cid, $name, $optOrder);
                if (!$optStmt->execute()) { $conn->rollback(); die("  option $sec/$name FAILED - {$conn->error}\n"); }
            }
        }
    }
    $conn->commit();
    echo "  seeded\n";
} else {
    echo "  would seed the above\n";
}

// ===== STEP 3: report saved values not in the lists =====
echo "\n--- Step 3: saved tna.training values not in the lists (info only) ---\n";
$known = [];
foreach ($seed as $sec => $groups) foreach ($groups as $names) foreach ($names as $n) $known[$sec][$n] = true;
$found = 0;
$res = $conn->query("SELECT section, training, COUNT(*) n FROM tna WHERE training IS NOT NULL AND training <> '' AND training <> 'OTHERS' GROUP BY section, training ORDER BY section, training");
while ($row = $res->fetch_assoc()) {
    if (!isset($known[$row['section']][$row['training']])) {
        echo "  {$row['section']}\t{$row['training']}\t({$row['n']} row(s))\n";
        $found++;
    }
}
echo $found ? "  -> these stay selectable on existing TNAs (hidden for new rows)\n" : "  none\n";

echo "\nDone." . ($apply ? '' : " Nothing was written - re-run with --apply (CLI) or &apply=1 (HTTP).") . "\n";
