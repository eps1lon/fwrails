<?php
require_once 'common.cron.php';
require_once 'curlm.class.php';

define('MAX_QUERIES', 10000); // max_allowed_pack

function curlm_exec($info) {
    if ($info['result'] !== CURLE_OK) {
        $info2 = curl_getinfo($info['handle']);
        echo "got " . Curlm::info_status($info['result']) . "for: ".
             $info2['url'];
    }
}

// achievements
$achievements = array();
$sql_query = "SELECT * FROM achievements ORDER BY achievement_id ASC, stage DESC";
$result = error_query($sql_query, $db);
while ($achievement = mysql_fetch_assoc($result)) {
    $achievement['id'] = $achievement['achievement_id'];
    
    if (!isset($achievements[$achievement['id']])) {
        $achievements[$achievement['id']] = array();
    }
    
    $achievements[$achievement['id']][$achievement['stage']] = $achievement;
}

// worlds only german
$worlds_all = array();
$world_ids = array();
$sql_query = "SELECT worlds.*, languages.tld FROM worlds, languages ".
             "WHERE languages.id = '1' AND worlds.language_id = languages.id";
$result = error_query($sql_query, $db);
while ($world = mysql_fetch_assoc($result)) {
    $worlds_all[$world['id']] = $world;
    $world_ids[] = $world['id'];
}
// af
$worlds_wout_dumps = [15 => $worlds_all[15]];
// all others except _wout_dumps
$worlds_with_dumps = array_diff_key($worlds_all, $worlds_wout_dumps);

// empty old progress table
$sql_query = "TRUNCATE TABLE users_achievements_old";
error_query($sql_query, $db);

// backup progress for later check of difference
$sql_query = "INSERT INTO users_achievements_old SELECT * FROM users_achievements";
error_query($sql_query, $db);

/* empty progress-table
$sql_query = "TRUNCATE TABLE users_achievements_progresses";
error_query($sql_query, $db);//*/

// current achievement number
$sql_query = "SELECT COUNT(*) FROM users_achievements";
$result_number = error_query($sql_query, $db);
$number_fetch = mysql_fetch_row($result_number);
$achievement_number = $number_fetch[0];

$worlds = $worlds_with_dumps;
$world_ids = implode(",", array_keys($worlds_with_dumps));
echo "crawling dumps in $world_ids\n";
include 'achievements_dumps.php';
echo "$sql_i_progress achievement_progresses inserted\n";

$worlds = $worlds_wout_dumps;
$world_ids = implode(",", array_keys($worlds_wout_dumps));
$full = ((int)strftime("%H")) === 0; // Midnight
echo "crawling profiles in $world_ids\n";
include 'achievements_profiles.php';
echo "$sql_i_progress achievement_progresses inserted\n";

// create dumps
create_dumps($db);

// new achievement number
$sql_query = "SELECT COUNT(*) FROM users_achievements";
$result = error_query($sql_query, $db);
$number_fetch = mysql_fetch_row($result);
echo ($number_fetch[0] - $achievement_number) . " new; $achievement_number current\n";

/* update counter_cache, sum_cache */

$sql_query = "TRUNCATE TABLE  users_achievements_caches";
error_query($sql_query, $db);

$primary_old = '0-0';
$i = 0;

function achievements_cache($primary, $count, $reward, $now, $db = null, $finish = false)  {
    static $insert_query = "INSERT INTO users_achievements_caches (user_id, world_id, count, reward_collected, created_at) VALUES ";
    static $sql_query = '';
    static $i = 0;
    
    $primaries = explode("-", $primary);

    implode_runtime($sql_query, $i, $insert_query, $db);  
    $sql_query .= "(" . mysql_encode(+$primaries[0], $db) . ", ".
                  "" . mysql_encode(+$primaries[1], $db) . ", ".
                  "" . mysql_encode($count, $db) . ", ".
                  "" . mysql_encode($reward, $db) . ", ".
                  "" . mysql_encode($now, $db) . ")";
    ++$i;
    
    if ($finish === true) {
        error_query($sql_query, $db);
        echo "updated $i caches\n";
    }
}
$count = 0;
$reward_collected = 0;
        
$sql_query = "SELECT user_id, world_id, achievement_id, ".
             "stage FROM users_achievements ".
             "ORDER BY user_id, world_id";
$result = error_query($sql_query, $db);
while ($achievement = mysql_fetch_assoc($result)) {
    $primary = $achievement['user_id'] . "-" . $achievement['world_id'];
    $stage = $achievement['stage'];
    
    if ($primary !== $primary_old) {     
        if ($primary_old !== '0-0') {
            achievements_cache($primary_old, $count, $reward_collected, $now, $db);
        }

        $count = 0;
        $reward_collected = 0;
    } 
    
    $count += $stage;
    for ($j = 1; $j <= $stage; ++$j) {
        $reward_collected += $achievements[$achievement['achievement_id']][$stage]['reward'];
    }
    
    $primary_old = $primary;
}
achievements_cache($primary_old, $count, $reward_collected, $now, $db, true);

// achievement progress changes
$sql_query = "SELECT new_table.user_id, new_table.world_id, ".
             "new_table.progress AS new, " .
             "new_table.achievement_id " .
             "FROM users_achievements new_table, users_achievements_old old_table " .
             "WHERE new_table.user_id = old_table.user_id AND ".
                   "new_table.world_id = old_table.world_id AND ".
                   "new_table.achievement_id = old_table.achievement_id AND ".
                   "new_table.progress != old_table.progress " .
             "GROUP BY new_table.user_id, new_table.world_id, new_table.achievement_id";
$result = error_query($sql_query, $db);
if ($diff = mysql_fetch_assoc($result)) {
    $i = 0;
    $insert_query = "INSERT INTO users_achievements_changes ".
                    "(user_id, world_id, achievement_id, ". 
                    "progress, created_at) VALUES ";

    do {
        implode_runtime($sql_query, $i, $insert_query, $db);       

        $sql_query .= "('" . $diff['user_id'] . "', '" . $diff['world_id'] . "',". 
                      " '" . $diff['achievement_id'] . "', " . mysql_encode($diff['new'], $db) . ", ".
                      "'$now')";

        $i += 1;
    } while ($diff = mysql_fetch_assoc($result));

    if ($sql_query !== $insert_query) { // remaining package
        error_query($sql_query, $db);
    }

    echo "$i progress-changes\n";
} else {
    echo "no progress-changes\n";
}

include "achievements_worlds.php";