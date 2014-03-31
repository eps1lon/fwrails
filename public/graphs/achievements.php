<?php
require_once '../../lib/php/graphs/graphs.inc.php';
error_reporting(E_ALL);

function user_from_param($param) {
    $primaries = explode("-", $param);
    
    if (count($primaries) < 2) {
        return null;
    }
    
    return [
        'user_id' => +$primaries[0],
        'world_id' => +$primaries[1]
    ];
}

function user_to_param(Array $user) {
    return implode("-", [$user['user_id'], $user['world_id']]);
}

// get world_ids from the worlds with achievements
$world_ids = [];
$sql_query = "SELECT id FROM worlds WHERE language_id = 1";
$worlds = $db->query($sql_query);
while ($world = $worlds->fetch_assoc()) {
    $world_ids[] = $world['id'];
}
$achievement_worlds = "IN (" . implode(",", $world_ids) . ")";

// init data
$data = [];

$input = filter_var_array($_GET, [
    'achievement' => FILTER_VALIDATE_INT,
    'mode' => FILTER_DEFAULT,
    'users' => ['filter'  => FILTER_DEFAULT,
                 'flags'  => FILTER_FORCE_ARRAY],
    'worlds' => ['filter' => FILTER_DEFAULT,
                 'flags'  => FILTER_FORCE_ARRAY]
]);
// achievement
$achievement_id = $input['achievement'];
$sql_query = "SELECT achievement_id, name FROM achievements " .
             "WHERE achievement_id = '$achievement_id' LIMIT 1";
$result = $db->query($sql_query);

if ($achievement = $result->fetch_assoc()) {
    $achievement_id = $achievement['achievement_id'];
    $title = $achievement['name'];
} else {
    $achievement_id = null;
    $title = '??';
}

// graph mode
$mode = $input['mode'];

if ($mode == 'user') {
    $user_primaries = [];
    
    // escape userinput
    $users = $input['users'];
    if ($users) {
        for ($i = 0, $count = count($users); $i < $count; ++$i) {
            $user_param = user_from_param($users[$i]);
            
            if ($user_param !== null) {
                $user_primaries[] = "(user_id = {$user_param['user_id']} " .
                                    "AND world_id = {$user_param['world_id']})";
            }
        }
    }
    
    if (!empty($user_primaries)) {
        // legend
        $sql_query = "SELECT users.name, worlds.short, users.user_id, users.world_id " .
                     "FROM users, worlds " .
                     "WHERE (" . implode(" OR ", $user_primaries) . ")" .
                     "AND users.world_id = worlds.id";
        $result = $db->query($sql_query);
        while ($user = $result->fetch_assoc()) {
            $legend[user_to_param($user)] = "{$user['name']} ({$user['short']})";
        }
        
        // get data, TODO: time distance
        $sql_query = "SELECT user_id, world_id, progress, " .
                     "UNIX_TIMESTAMP(created_at) as created_at " .
                     "FROM users_achievements_changes ".
                     "WHERE (" . implode(" OR ", $user_primaries) . ")" .
                     "AND achievement_id = '$achievement_id'";
        $changes = $db->query($sql_query);
        while ($change = $changes->fetch_assoc()) {
            $data[user_to_param($change)][$change['created_at']] = $change['progress'];
        }
    }

} else if ($mode == 'world') {
    $world_ids = [];
    // TODO: all worlds
    
    // escape userinput
    $worlds = $input['worlds'];
    if ($worlds) {
        $world_shorts = array_map([$db, 'escape_string'], $worlds);
        
        if ($world_shorts[0]) {
            // get world_ids
            $sql_query = "SELECT id, short FROM worlds WHERE id $achievement_worlds " .
                         "AND short IN ('" . implode("','", $world_shorts) . "')";
            $worlds = $db->query($sql_query);
            while ($world = $worlds->fetch_assoc()) {
                $world_ids[] = $world['id'];

                // init data group
                $data[$world['id']] = [];

                // legend mapper
                $legend[$world['id']] = $world['short'];
            }
          
            $sql_query = "SELECT world_id, progress, " .
                         "UNIX_TIMESTAMP(created_at) as created_at " .
                         "FROM worlds_achievements_changes ".
                         "WHERE world_id IN (" . implode(",", $world_ids) . ") " .
                         "AND achievement_id = '$achievement_id'";
        } else {
            $legend["all_worlds"] = "Alle Welten";
            
            $sql_query = "SELECT 'all_worlds' AS world_id, SUM(progress) as progress, " .
                         "UNIX_TIMESTAMP(created_at) as created_at " .
                         "FROM worlds_achievements_changes ".
                         "WHERE achievement_id = '$achievement_id' " .
                         "GROUP BY created_at";
        }
        
        
        // get data, TODO: time distance
        
        $changes = $db->query($sql_query);
        while ($change = $changes->fetch_assoc()) {
            $data[$change['world_id']][$change['created_at']] = $change['progress'];
        }
        
        // map ksort, faster then order by in sql statement
        foreach ($data as &$world_data) {
            ksort($world_data, SORT_NUMERIC);
        }
    } else {
        // graph error no worlds TODO
    }   
} else {
    // jpgraph error TODO
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
    'left'   => 60,
    'top'    => 30,
    'right'  => 5,
    'bottom' => 30
];
$graph_dimensions_mode = DIMENSIONS_FIXED_GRAPH;

$graph_tick_count_major = 6;
$graph_tick_count_minor = 12;

// draw graph
include RAILS_ROOT . '/lib/php/graphs/graphs.scaffold.php';