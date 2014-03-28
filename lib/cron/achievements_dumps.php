<?php
// included in achievements.php

// curlm vars
$max_curl_connections = 20;

$curlopts = array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_CONNECTTIMEOUT => 0,
    CURLOPT_TIMEOUT => 0
);

$curl_i = 0;
$curl_i_length = count($worlds);

$curlm = new Curlm();
$curlm->setopts($curlopts);

function get_world($url, $worlds) {
    preg_match("/http:\/\/(\w+)\.freewar\.(\w+)/", $url, $domain);
    
    $subdomain = $domain[1];
    $tld = $domain[2];
    
    foreach ($worlds as $world) {
        if ($world['subdomain'] == $subdomain && $world['tld'] = $tld) {
            return $world;
        }
    }
    
    return null;
}

function get_stage($progress, $achievement_id, $achievements) {
    if (isset($achievements[$achievement_id])) {
        for ($stage = 1; $stage <= $achievements[$achievement_id][1]['max_stage']; ++$stage) {
            $achievement = $achievements[$achievement_id][$stage];
            if (is_null($achievement['needed']) || $progress < $achievement['needed']) {
                break;
            }  
        }
        
        return $stage - 1;
    }
    
    return 0;
}

function curlm_each_dump($dump_plain, $handle, $j) {
    global $worlds, $sql_i_progress, $sql_query_progress, $insert_query_progress,
           $now, $db, $achievements;
    
    $info = curl_getinfo($handle);
    if (in_array(substr($info['http_code'], 0, 1), array('4', '5'))) { // server or client error
        echo "got http_code #" . $info['http_code'] . " on {$info['url']}\n";
        return true;
    }
    
    $world = get_world($info['url'], $worlds);
    
    if ($world == null) {
        echo "could extract world from {$info['url']} and ";
        print_r($worlds);
        return true;
    }
    
    // save
    file_put_contents(SHARED_PATH . "public/dumps/achievements/{$world['subdomain']}.txt", $dump_plain);
    
    $rows = explode("\n", $dump_plain);
    
    foreach ($rows as $row) {
        $values = explode("\t", trim($row));
        
        if (count($values) < 3) {
            continue;
        }
        
        $user_id        = $values[0];
        $achievement_id = $values[1];
        $progress       = $values[2];
        
        if (!$user_id || !$achievement_id || !$progress) {
            continue;
        }
        
        // TODO get stage
        $stage = get_stage($progress, $achievement_id, $achievements);
        
        implode_runtime($sql_query_progress, $sql_i_progress, 
                        $insert_query_progress, $db);
                
        // achievement_id, stage
        // user_id, world_id
        // progress, created_at
        $sql_query_progress .= "('$achievement_id', '$stage', ".
                                "'$user_id', '{$world['id']}', ".
                                "'$progress', '$now')";
        
        ++$sql_i_progress;
    }
    
    
}

$sql_i_progress = 0;
$insert_query_progress = "REPLACE INTO users_achievements (achievement_id, ".
                         "stage, user_id, world_id, progress, created_at) VALUES ";
$sql_query_progress = $insert_query_progress;

// loop through each world
foreach ($worlds as $world_id => $world) {
    // change get_world if you change the pattern
    $dump_url = "http://{$world['subdomain']}.freewar.{$world['tld']}" .
                "/freewar/dump_achieves.php";
                
    // add handle
    $curlm->add_handle($dump_url);
    
    if (($curl_i + 1) % $max_curl_connections === 0 || $curl_i === $curl_i_length - 1) { // max curl connections
        // exec
        $curl_t = $curlm->exec("curlm_exec");
        
        echo "crawled " . (($curl_i + 1) % $max_curl_connections).
             " dumps in " . ($curl_t * 1000) . "ms\n";
        
        $curlm->each("curlm_each_dump");
        
        unset($curlm);
        $curlm = new Curlm();
        $curlm->setopts($curlopts);
    }
    
    $curl_i++;
}

if ($sql_query_progress !== $insert_query_progress) { // remaining package
    error_query($sql_query_progress, $db);
}