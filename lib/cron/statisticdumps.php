<?php
require 'common.cron.php';

define('MAX_QUERIES', 10000); // max_allowed_pack

// get statistic tokens
$sql_query = "SELECT id, name FROM statistics";
$result = error_query($sql_query, $db);
while ($statistics = mysql_fetch_assoc($result)) {
    $tokens[$statistics['id']] = $statistics['name'];
}

$insert_query = "INSERT INTO statistic_changes (statistic_id, world_id, value, created_at) VALUES ";
$changes = 0;

// loop through each World
$sql_query = "SELECT worlds.id, languages.tld, worlds.subdomain ". 
             "FROM worlds, languages ".
             "WHERE worlds.language_id = languages.id";
$worlds = error_query($sql_query, $db);
while ($world = mysql_fetch_assoc($worlds)) {   
    $dump_uri_dest = SHARED_PATH . 'public/dumps/statistics/' . $world['subdomain'] . '.txt';
    $dump_uri_src = "http://" . $world['subdomain'] . ".freewar." . $world['tld'] . "/freewar/list_stats.php";
    #$dump_uri_src = $dump_uri_dest; // development
    
    // get dump
    $dump = trim(file_get_contents($dump_uri_src));
    if (empty($dump)) {
        echo "empty dump\n";
        continue;
    }
    
    // and save it
    file_put_contents($dump_uri_dest, $dump);
    chmod($dump_uri_dest, 0664);
    
    // loop through each line
    $lines = explode("\n", $dump);
    for ($i = 0, $length = count($lines); $i < $length; $i += 1) {
        $line = $lines[$i]; // helper
        if (empty($line)) {
            echo "empty_line\n";
            continue;
        }               
       
        $data = explode("\t", $line);
        if (count($data) < 2) {
            echo "$line => count(data) < 2\n";
            continue;
        }
        
        $statistic_id = array_search($data[0], $tokens);
        if ($statistic_id === false) { // new statistic
            error_query("INSERT INTO statistics (name, created_at) VALUES ('" . $data[0] . "', '$now')", $db);
            $statistic_id = mysql_insert_id($db);
        }
        
        $change_query = "SELECT value ".
                        "FROM statistic_changes ".
                        "WHERE statistic_id = '$statistic_id' AND world_id = '" . $world['id'] . "' ".
                        "ORDER BY created_at DESC LIMIT 1";
        $result = error_query($change_query, $db);
        $old = mysql_fetch_row($result);
        if ($old[0] != $data[1]) { // change? === true
            implode_runtime($sql_query, $changes, $insert_query, $db);
            
            $sql_query .= "('$statistic_id', '" . $world['id'] . "',".
                          " '" . (int)$data[1] . "', '$now')";
            $changes += 1;
        }
    }    
    
    echo "$i statistics in " . $world['subdomain'] . "\n";
}
echo "$changes changes\n";

if ($sql_query !== $insert_query) { // remaining package
    error_query($sql_query, $db);
}