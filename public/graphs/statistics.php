<?php
require_once '../../lib/php/graphs/graphs.inc.php';
error_reporting(E_ALL);

// init data
$data = [];

$input = filter_var_array($_GET, [
    'statistic' => FILTER_VALIDATE_INT,
    'worlds' => ['filter' => FILTER_DEFAULT,
                 'flags'  => FILTER_FORCE_ARRAY]
]);

// achievement
/* @var $statistic_id int */
$statistic_id = $input['statistic'];
$sql_query = "SELECT name FROM statistics WHERE id = '$statistic_id'";
$result = $db->query($sql_query);

if ($statistic = $result->fetch_assoc()) {
    // get i18n names
    $locale_file = RAILS_ROOT . "/config/locales/statistics.names.$locale.yml";
    $names = yaml_parse_file($locale_file)[$locale]['statistics']['names'];   
    
    // translate
    $title = isset($names[$statistic['name']]) ? $names[$statistic['name']] : $statistic['name'];
} else {
    $statistic_id = null;
    $title = '??';
}

$worlds = $input['worlds'];
if ($worlds) {
    $world_ids =  [];
    $world_shorts = array_map([$db, 'escape_string'], $worlds);

    if ($world_shorts[0]) {
        // get world_ids
        $sql_query = "SELECT id, short FROM worlds WHERE " .
                     "short IN ('" . implode("','", $world_shorts) . "')";
        $worlds = $db->query($sql_query);
        while ($world = $worlds->fetch_assoc()) {
            $world_ids[] = $world['id'];

            // init data group
            $data[$world['id']] = [];

            // legend mapper
            $legend[$world['id']] = $world['short'];
        }

        $sql_query = "SELECT world_id, value, " .
                     "UNIX_TIMESTAMP(created_at) as created_at " .
                     "FROM statistic_changes ".
                     "WHERE world_id IN (" . implode(",", $world_ids) . ") " .
                     "AND statistic_id = '$statistic_id'";
    } else {
        $legend["all_worlds"] = "Alle Welten";

        $sql_query = "SELECT 'all_worlds' AS world_id, SUM(value) as value, " .
                     "UNIX_TIMESTAMP(created_at) as created_at " .
                     "FROM statistic_changes ".
                     "WHERE statistic_id = '$statistic_id' " .
                     "GROUP BY created_at";
    }
    // get data, TODO: time distance

    $changes = $db->query($sql_query);
    while ($change = $changes->fetch_assoc()) {
        $data[$change['world_id']][$change['created_at']] = $change['value'];
    }

} else {
    // graph error no worlds TODO
}   

// map ksort, faster then order by in sql statement
foreach ($data as &$group_data) {
    ksort($group_data, SORT_NUMERIC);
}
unset($group_data); // break the reference with the last element

// graph settings

// dimensions
$width = 600;
$height = 300;
$margin = [
    40,
    0,
    15,
    0
];
$graph_dimensions_mode = DIMENSIONS_FIXED_GRAPH;

$graph_tick_count_major = 6;
$graph_tick_count_minor = 12;
// draw graph
include RAILS_ROOT . '/lib/php/graphs/graphs.scaffold.php';