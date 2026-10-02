<?php
// Shared source for the TNA "Training Required" dropdowns.
//
// The option lists used to be hardcoded HTML, copied 3 times into each of the
// 7 TNA form pages. They now live in the tna_training_category /
// tna_training_option tables and are managed by admins from
// admin/tna/training_options.php. Each form page calls
// tna_training_options_script() once in <head>, which exposes
// TNA_TRAINING_OPTIONS.<section> as ready-made <option> HTML for the JS row
// templates to drop into their <select>.
//
// Hidden options, and any value already saved in tna.training that is no
// longer in the table at all, are still emitted but with the `hidden`
// attribute: staff cannot pick them for new rows, but an existing TNA row
// still finds its saved value when it is opened for editing. Without that the
// select comes up empty, nothing is posted for that row, and because
// tna_action.php deletes and re-inserts every row on save, the row is lost.

require_once __DIR__ . '/dbconn.php';

const TNA_TRAINING_OTHERS = 'OTHERS';

// tna.section key => label, in form order (a-g).
function tna_training_sections()
{
    return [
        'esgaware'   => 'a. ESG (Environment-Social-Governance)',
        'selfaware'  => 'b. Soft Skill',
        'leadaware'  => 'c. Leadership Awareness',
        'dataaware'  => 'd. Data Driven',
        'functional' => 'e. Functional Awareness',
        'busiaware'  => 'f. Digital Transformation & Innovation',
        'special'    => 'g. Special Project',
    ];
}

// Normalises an option or group name the way it is stored (uppercase,
// single-spaced). Returns [name, error|null]. Apostrophes are fine (saves use
// prepared statements, see tna_save.php); double quotes, backslashes and < >
// are refused because other TNA pages echo tna.training unescaped.
function tna_training_clean_name($raw)
{
    $name = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $raw)));
    if ($name === '') return [$name, 'Name is required.'];
    if (strlen($name) > 255) return [$name, 'Name is too long (max 255 characters).'];
    if (preg_match('/["\\\\<>]/', $name)) return [$name, 'Name cannot contain double quotes, backslashes or < >.'];
    if ($name === TNA_TRAINING_OTHERS) return [$name, 'OTHERS is added automatically to every list.'];
    return [$name, null];
}

function tna_training_h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Returns [section => option HTML] for every section.
function tna_training_options_html_all($conn)
{
    $sections = tna_training_sections();
    $cats = [];   // section => [category_id => name], in display order
    $opts = [];   // section => category_id|0 => [[name, active], ...]
    $known = [];  // section => [name => true]

    $res = $conn->query("SELECT id, section, name FROM tna_training_category ORDER BY section, sort_order, id");
    while ($row = $res->fetch_assoc()) {
        $cats[$row['section']][(int) $row['id']] = $row['name'];
    }

    $res = $conn->query("SELECT section, category_id, name, is_active FROM tna_training_option ORDER BY section, sort_order, id");
    while ($row = $res->fetch_assoc()) {
        $opts[$row['section']][(int) $row['category_id']][] = [$row['name'], (int) $row['is_active'] === 1];
        $known[$row['section']][$row['name']] = true;
    }

    // Saved values that are not in the table any more (legacy names, or a
    // rename that raced with someone saving an open form).
    $orphans = [];
    $res = $conn->query("SELECT DISTINCT section, training FROM tna WHERE training IS NOT NULL AND training <> ''");
    while ($row = $res->fetch_assoc()) {
        $sec = $row['section'];
        if (isset($sections[$sec]) && $row['training'] !== TNA_TRAINING_OTHERS && !isset($known[$sec][$row['training']])) {
            $orphans[$sec][] = $row['training'];
        }
    }

    $out = [];
    foreach ($sections as $sec => $label) {
        $render = function ($list) {
            $html = '';
            foreach ($list as $o) {
                $html .= '<option value="' . tna_training_h($o[0]) . '"' . ($o[1] ? '' : ' hidden') . '>' . tna_training_h($o[0]) . '</option>';
            }
            return $html;
        };
        $others = '<option value="' . TNA_TRAINING_OTHERS . '">' . TNA_TRAINING_OTHERS . '</option>';

        // Ungrouped options first, then each group with its own OTHERS at the
        // bottom (same layout the hardcoded Functional list had).
        $html = $render($opts[$sec][0] ?? []);
        foreach ($cats[$sec] ?? [] as $cid => $cname) {
            $html .= '<optgroup label="' . tna_training_h($cname) . '">' . $render($opts[$sec][$cid] ?? []) . $others . '</optgroup>';
        }
        if (empty($cats[$sec])) {
            $html .= $others;
        }
        foreach ($orphans[$sec] ?? [] as $name) {
            $html .= '<option value="' . tna_training_h($name) . '" hidden>' . tna_training_h($name) . '</option>';
        }
        $out[$sec] = $html;
    }
    return $out;
}

// <script> defining TNA_TRAINING_OPTIONS for the form pages.
function tna_training_options_script($conn)
{
    $json = json_encode(
        tna_training_options_html_all($conn),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
    );
    return "<script>var TNA_TRAINING_OPTIONS = $json;</script>\n";
}
