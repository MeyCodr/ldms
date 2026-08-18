<?php
// Whitelist of browsable archive tables (created by scripts/archive_2025_and_below.php).
// Only entries in this map may ever be used as a table/column name in a query -
// never trust $_POST/$_GET directly for identifiers.
$ARCHIVE_ENTITIES = [
    'training' => [
        'table' => 'training_archive',
        'date_column' => 'startdate',
        'label' => 'Training (Public / Inhouse)',
    ],
    'ojt' => [
        'table' => 'ojt_archive',
        'date_column' => 'startdate',
        'label' => 'OJT',
    ],
    'tna' => [
        'table' => 'tna_archive',
        'date_column' => 'dateapprove',
        'label' => 'TNA',
    ],
    'pme' => [
        'table' => 'pme_archive',
        'date_column' => 'from_date',
        'label' => 'PME',
    ],
    'tni' => [
        'table' => 'tni_archive',
        'date_column' => 'year',
        'label' => 'TNI',
    ],
    'certificate' => [
        'table' => 'certificate_archive',
        'date_column' => 'upload_date',
        'label' => 'Certificate',
    ],
    // Not a single archive table - a join across training_archive/participation_archive
    // and ojt_archive/participateojt_archive keyed by userid. Handled specially in
    // fetch_archive.php / export_archive.php (see 'participant_history' branch); the
    // 'type' key below is how those files recognise it and skip the generic
    // single-table SHOW COLUMNS path.
    'participant' => [
        'type' => 'participant_history',
        'label' => 'Participant Training History',
    ],
];

// Who can view/filter/download archive data: ADMIN, or any Clerk-Main user
// (role CLERK / usertype MAIN) - independent of SKILL MATRIX / manager
// designation. Clerk-Main is the only non-admin portal whose session data
// actually distinguishes it from its sibling portal (clerk/general uses
// role CLERK without requiring usertype MAIN); staff/hod, staff/office and
// staff/general all share the same role=='' session check with no way to
// tell them apart server-side, so staff access was deliberately left out.
function archiveUserCanAccess()
{
    if (!isset($_SESSION['fullname'], $_SESSION['role'])) {
        return false;
    }
    if ($_SESSION['role'] == 'ADMIN') {
        return true;
    }
    if ($_SESSION['role'] == 'CLERK' && isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'MAIN') {
        return true;
    }

    return false;
}
