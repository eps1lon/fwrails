<?php
// included in achievements.php

$max_curl_connections = 800;
define('PATTERN_CAPTION', '<p class="maincaption2">.+?<\/p>');

function curlm_each_profile($html, $handle, $j) {
    global $db, $sql_query_progress, $insert_query_progress, 
           $sql_i, $sql_i_progress, $users, $now;
    
    // array with all achievements
    $achievements = array();
    
    #echo $users[$j]['user_id'] . " in world " . $users[$j]['world_id'] . ":";
    #echo $html;
    
    if (empty($html) && !defined('EMPTY_PROFILE')) {
        define('EMPTY_PROFILE', 1);
        return true;
    }
    
    $info = curl_getinfo($handle);
    if (in_array(substr($info['http_code'], 0, 1), array('4', '5'))) { // server or client error
        echo "got http_code #" . $info['http_code'] . "\n";
        return true;
    }
    
    // area with achieved achievements
    preg_match('/' . PATTERN_CAPTION . '(.+?)' . PATTERN_CAPTION . '(.*)/i', 
               $html, $achiev_groups);
    #print_r($achiev_groups);
    
    #$achieved = $matches[1];
    #$unachieved = $matches[2];
    
    foreach ([1, 2] as $matches_key) {
        // get each achievement
        preg_match_all('/<a name="achiev(\d+)s(\d+)"><\/a>.*?<\/p>(.*?)<\/p>/i', 
                        $achiev_groups[$matches_key], $matches);
        #print_r($matches);
    
        for ($k = 0, $k_length = count($matches[1]); $k < $k_length; ++$k, ++$sql_i) {
            $achievement_id = $matches[1][$k];
            
            // progress
            if (!preg_match("/<b>([0-9\.]+)<\/b> von /i", $matches[3][$k], $progress)) { // "progressable" achievement
                $progress = array(0, 0); // progress = 0
            } else {
                $progress[1] = (int)str_replace(".", "", $progress[1]);
            }
            
            // stage
            if ($matches_key == 1) { // achieved
                $stage = max(1, (int)$matches[2][$k]);
            } else { // unachieved
                $stage = max(0, $matches[2][$k] - 1);
            }
            
            // only save non-empty
            if ($stage > 0 || $progress[1] > 0) {
                // save
                $achievements[$achievement_id] = [
                    'stage' => $stage,
                    'progress' => $progress[1]
                ];
            }
        }
    }
    
    // and insert into db
    foreach ($achievements as $achievement_id => $data) {
       implode_runtime($sql_query_progress, $sql_i_progress, 
                       $insert_query_progress, $db);
                
        // achievement_id, stage
        // user_id, world_id
        // progress, created_at
        $sql_query_progress .= "('$achievement_id', '" . $data['stage'] . "', ".
                                "'" . $users[$j]['user_id'] . "', '" . $users[$j]['world_id'] . "', ".
                                "'" . $data['progress'] . "', '$now')";
    
        ++$sql_i_progress;
    } 
}

$curlopts = array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_CONNECTTIMEOUT => 0,
    CURLOPT_TIMEOUT => 0
);

## deprecated
#$insert_query = "INSERT IGNORE INTO users_achievements ".
#                "(achievement_id, stage, user_id, world_id, created_at) VALUES ";
##
$insert_query_progress = "REPLACE INTO users_achievements (achievement_id, ".
                         "stage, user_id, world_id, progress, created_at) VALUES ";

$curlm = new Curlm();
$curlm->setopts($curlopts);

$users = array();

echo "full: " . strftime("%H") . " => "; var_dump($full);

$limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT);
if ($limit) {
    $steps = 10000;
    $sql_query = "SELECT user_id, world_id FROM users WHERE world_id IN ($world_ids) ".
                 "LIMIT " . ($limit * $steps) . ", $steps";
} else if ($full === true) {
    $sql_query = "SELECT user_id, world_id FROM users WHERE world_id IN ($world_ids)";
} else {
    $sql_query = "SELECT user_id, world_id FROM users_experience_changes ".
                 "WHERE world_id IN ($world_ids) AND created_at > DATE_SUB(NOW(), INTERVAL '1 3' DAY_HOUR) ".
                 "GROUP BY user_id, world_id";
    
    $change_limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT);
    if ($change_limit) {
        $sql_query .= " LIMIT $change_limit";
    }
}
#$sql_query = "SELECT user_id, world_id FROM users WHERE world_id IN ($world_ids) AND user_id = 56777 AND world_id = 3 ORDER BY RAND() LIMIT 1";
$result = error_query($sql_query, $db);

// init counter vars
$curl_i = 0;
$sql_i = 0;
$sql_i_progress = 0;
$curl_i_length = mysql_num_rows($result);
$new = 0;

echo "fetching $curl_i_length profiles\n";

$sql_query_progress = $insert_query_progress;
## deprecated
#$sql_query = $insert_query;
##

// loop through each user
while ($user = mysql_fetch_assoc($result)) {
    $uri = "http://www." . $worlds[$user['world_id']]['subdomain'] . ".freewar." . $worlds[$user['world_id']]['tld'] . 
           "/freewar/internal/achiev.php?act_user_id=" . $user['user_id'];
    #$uri = "http://localhost/ror/freewar3/public/examples/achiev.php";
    #echo "$uri\n";
    
    // add handle
    $curlm->add_handle($uri);

    // save userdata
    $users[] = $user;
    
    if (($curl_i + 1) % $max_curl_connections === 0 || $curl_i === $curl_i_length - 1) { // max curl connections
        // exec
        $curl_t = $curlm->exec("curlm_exec");
        
        echo "crawled " . (($curl_i + 1) % $max_curl_connections).
             " profiles in " . ($curl_t * 1000) . "ms\n";
        
        $curlm->each("curlm_each_profile");
        
        unset($curlm);
        $curlm = new Curlm();
        $curlm->setopts($curlopts);
        
        $users = array();
    }
    
    $curl_i++;
}
## deprecated
#if ($sql_query !== $insert_query) { // remaining package
#    error_query($sql_query, $db);
#}
##
if ($sql_query_progress !== $insert_query_progress) { // remaining package
    error_query($sql_query_progress, $db);
}

if (defined('EMPTY_PROFILE')) {
    echo "there was at least 1 empty curl response\n";
}

echo "$sql_i achievements traversed; ";