<?php
require_once dirname(__FILE__) . '/../rails_const.php';
require_once RAILS_ROOT . '/lib/php/db.php';

define('JPGRAPH_INCLUDE', RAILS_ROOT . '/lib/php/jpgraph/src');

require_once JPGRAPH_INCLUDE . '/jpgraph.php';
require_once JPGRAPH_INCLUDE . '/jpgraph_line.php';
require_once JPGRAPH_INCLUDE . '/jpgraph_date.php';
require_once JPGRAPH_INCLUDE . '/jpgraph_utils.inc.php';
require_once JPGRAPH_INCLUDE . '/jpgraph_table.php';

$locale = 'de';

if (RAILS_ENV == 'production') {
    JpGraphError::SetErrLocale('de.fwrails');
}

// we only need the mysqli adapter
$db = $dbi;

// Dimension calculation
define('DIMENSIONS_FIXED_GRAPH', 1);
define('DIMENSIONS_FIXED_IMAGE', 2);

// graph library included
define('GRAPH_INCLUDED', true);

// functions
function number_in_magnitudes($i, $precision = 1) {
    $magnitudes = [
        3 => 'k',
        6 => 'M',
        9 => 'T'
    ];
    krsort($magnitudes);
    
    $exponent = strlen((string)(int)$i) - 1;
    
    foreach ($magnitudes as $magnitude => $postfix) {
        if ($magnitude <= $exponent) {
            return round($i / pow(10, $magnitude), $precision) . $postfix;
        }
    }
    
    return round($i, $precision);
}

function data_stats($data) {
    $max = max($data);
    $min = min($data);
    $avg = ($max + $min) / 2;
    
    return [
        "cur" => end($data),
        "max" => $max,
        "min" => $min,
        "avg" => $avg
    ];
}

// parse base64
if ($base64 = filter_input(INPUT_GET, 'base64')) {
    parse_str(base64_decode($base64), $_GET);
}

// std values
$title = '';
$subtitle = '';
$data = [];
$date_format = 'j.n.y';
$legend = [];