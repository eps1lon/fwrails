<?php
require_once '../php/rails_const.php';
require_once '../php/db.php';

error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf8');

function cache_sum($sum, $timestamp, $db) {
    $values = [];
    
    #echo date(DATE_RSS, $timestamp) . "\n"; print_r($sum);
    
    foreach ($sum as $achievement_id => $world_sum) {
       foreach ($world_sum as $world_id => $progress) {
           $value = "($achievement_id, $world_id, FROM_UNIXTIME($timestamp), ".
                    "$progress)";
           $values[] = $value;
       } 
    } 
    
    $sql_query = "REPLACE INTO worlds_achievements_changes " .
                 "(achievement_id, world_id, created_at, progress) ".
                 "VALUES " . implode(",", $values); 
    $db->query($sql_query);
}

// we only need the mysqli adapter
$db = $dbi;

// init
$sql_query = "TRUNCATE TABLE worlds_achievements_changes";
$db->query($sql_query);

// progress sum
$sum = [];
// achievement_worlds
$world_ids = [];
$sql_query = "SELECT id FROM worlds WHERE language_id = 1";
#$sql_query .= " AND id = 3";
$worlds = $db->query($sql_query);
while ($world = $worlds->fetch_assoc()) {
    $world_ids[] = $world['id'];
}
$where_worlds = "world_id IN (" . implode(",", $world_ids) . ")";

$where_achievements = 1;//"achievement_id = 15";

// init world_sums
$sql_query = "SELECT DISTINCT achievement_id as id FROM users_achievements WHERE $where_achievements";
$achievement_ids = $db->query($sql_query);
while ($achievement_id = $achievement_ids->fetch_assoc()) {
    $sum[$achievement_id['id']] = array_fill_keys($world_ids, 0);
}

$current = [];
$created_at = 0;
// init current state
$sql_query = "SELECT user_id, world_id, achievement_id, progress, ".
                    "UNIX_TIMESTAMP(created_at) as created_at ".
             "FROM users_achievements WHERE $where_worlds AND $where_achievements";
$progresses = $db->query($sql_query);
while ($p = $progresses->fetch_assoc()) {
    if ($p['created_at'] > $created_at) {
        $created_at = $p['created_at'];
    }
    
    $current[$p['achievement_id']][$p['world_id']][$p['user_id']] = $p['progress'];
    $sum[$p['achievement_id']][$p['world_id']] += $p['progress'];
}

$warnings = 0;

// get changes
$sql_query = "SELECT user_id, world_id, achievement_id, progress, " .
             "UNIX_TIMESTAMP(created_at) as created_at " .
             "FROM users_achievements_changes " .
             "WHERE $where_worlds AND $where_achievements " .
             "ORDER BY created_at DESC";
$changes = $db->query($sql_query);
while ($change = $changes->fetch_assoc()) {
    if ($created_at > $change['created_at']) {
        // cache current
        cache_sum($sum, $created_at, $db);
        $created_at = $change['created_at'];
    }
    
    $progress_change = $current[$change['achievement_id']][$change['world_id']][$change['user_id']]
                     - $change['progress'];
    
    if ($progress_change < 0) {
        echo implode("-", $change) . "\n";
        $warnings++;
    } else {
        // subtract change
        $sum[$change['achievement_id']][$change['world_id']] -= $progress_change;
        
    
        // save change
        $current[$change['achievement_id']][$change['world_id']][$change['user_id']] = $change['progress'];
    }
    
    
    
    
}
echo "skipped $warnings changes\n";