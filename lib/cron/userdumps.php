<?php
require 'common.cron.php';
/* @var $db mysql adapter */

define('MAX_QUERIES', 10000); // max_allowed_pack
define('MAX_CURL_CONNECTIONS', 50);

function race_id ($name) {
    static $races = false;
    static $race_count = 0;
    
    if ($races === false) {
        // get races
        $sql_query = "SELECT id, name FROM races";
        $result = error_query($sql_query, $GLOBALS['db']);
        while ($race = mysql_fetch_assoc($result)) {
            $races[] = array('id' => $race['id'], 'name' => $race['name']);
        }
        
        $races[] = array('id'=> 1, 'name' => 'Tarunan');
        $races[] = array('id'=> 2, 'name' => 'Dark Mage');
        $races[] = array('id'=> 3, 'name' => 'Natla');
        $races[] = array('id'=> 4, 'name' => 'Human Warrior');
        $races[] = array('id'=> 5, 'name' => 'Human Sorcerer'); 
        $races[] = array('id'=> 6, 'name' => 'Human Worker');
        // 7 => Onlo
        $races[] = array('id'=> 8, 'name' => 'Serum Wraith');  
        
        $race_count = count($races);
    }
    
    for ($i = 0; $i < $race_count; ++$i) {
        if ($races[$i]['name'] == $name) {
            return $races[$i]['id'];
        }
    }
    
    trigger_error("couldnt find race $name", E_USER_NOTICE);
    return false;
}

// empty users_old-table
$sql_query = "TRUNCATE TABLE users_old";
error_query($sql_query, $db);

// backup userdata for later check of difference
$sql_query = "INSERT INTO users_old SELECT * FROM users";
error_query($sql_query, $db);

// empty users-table
/* just delete the users from whom we get new data via dumps
$sql_query = "TRUNCATE TABLE users";
error_query($sql_query, $db);//*/

$skipped_worlds = array();

$recover_clan = array();

// loop through each World
$sql_query = "SELECT worlds.id, languages.tld, worlds.subdomain ". 
             "FROM worlds, languages ".
             "WHERE worlds.language_id = languages.id";
$worlds = error_query($sql_query, $db);
while ($world = mysql_fetch_assoc($worlds)) {
    $dump_uri_dest = SHARED_PATH . 'public/dumps/users/' . $world['subdomain'] . '.txt';
    $dump_uri_src = "http://" . $world['subdomain'] . ".freewar." . $world['tld'] . "/freewar/list_players.php";
    $dump_uri_src_full = "http://" . $world['subdomain'] . ".freewar." . $world['tld'] . "/freewar/dump_players.php";
    #$dump_uri_src = $dump_uri_dest; // development
    
    $world_id = +$world['id'];
    
    // get dump
    $dump_full = @trim(file_get_contents($dump_uri_src_full));
    
    if (!empty($dump_full)) {
        $dump = $dump_full;
    } else {
        $dump = @trim(file_get_contents($dump_uri_src));
        if (empty($dump)) {
            // Skipped worlds
            $skipped_worlds[] = $world_id;
            
            echo "empty dump\n";
            continue;
        } else {
            $recover_clan[] = $world['id'];
        }
    }

    $sql_query = "DELETE FROM users WHERE world_id = '$world_id'";
    error_query($sql_query, $db);
    
    // and save it
    file_put_contents($dump_uri_dest, $dump);
    
    $insert_query = "INSERT INTO users (user_id, name, experience, race_id, world_id, created_at, clan_id) VALUES ";
    
    // loop through each line
    $lines = explode("\n", $dump);
    for ($i = 0, $length = count($lines); $i < $length; $i += 1) {
        $line = $lines[$i]; // helper
        if (empty($line)) {
            echo "empty_line\n";
            continue;
        }               
       
        $data = explode("\t", $line);
        if (count($data) < 4) {
            continue;
        }
        
        implode_runtime($sql_query, $i, $insert_query, $db);
        
        $race_id = race_id($data[3]);##
        if ($race_id === false) {
            var_dump($data);
        }
        $clan_id = @(int)$data[4]; // extended dump
        
        $sql_query .= "(" . mysql_encode(+$data[0], $db) . ", ".        // user_id
                            mysql_encode(decode_str($data[1]), $db) . ", ".// name
                            mysql_encode(+$data[2], $db) . ", ".        // experience
                            mysql_encode($race_id, $db) . ", ".         // race_id
                            mysql_encode($world_id, $db) . ", '$now', $clan_id)"; // world_id, created_at
    }

    if ($sql_query !== $insert_query) { // remaining package
        error_query($sql_query, $db);
    }
    
    echo "inserted $i users in " . $world['subdomain'] . "\n";
    #break;
}

// recover clan
if (!empty($recover_clan)) {
    echo "recovered clans in " . implode(",", $recover_clan) . "\n";
    $sql_query = "UPDATE users, users_old SET users.clan_id = users_old.clan_id ".
                 "WHERE users.user_id = users_old.user_id " .
                 "AND users.world_id = users_old.world_id " .
                 "AND users.world_id IN (" . implode(",", $recover_clan) . ")";
    error_query($sql_query, $db);
}


// create dumps
create_dumps($db);

$update_queries = array();
$sql_queries = array();
$mark_tables = array("users_achievements_caches",
                     "users_achievements",
                     "users_achievements_changes", 
                     "users_clan_changes", 
                     "users_experience_changes", 
                     "users_name_changes",
                     "users_race_changes");
$mark_tables_count = count($mark_tables);
$backup_cols = array();

for ($i = 0; $i < $mark_tables_count; ++$i) {
    $update_queries[$i] = "UPDATE " . $mark_tables[$i] . " SET deleted = '0' WHERE ";
    $sql_queries[$i] = $update_queries[$i];
    
    $backup_cols[$i] = array();
    $cols = error_query("SHOW COLUMNS FROM " . $mark_tables[$i], $db);
    while ($col = mysql_fetch_assoc($cols)) {
        $backup_cols[$i][] = $col['Field'];
    }
    $backup_cols[$i] = implode(", ", $backup_cols[$i]);
}

// new Users
// select from users where is no corespondending in users_old
$sql_query = "SELECT a.user_id, a.world_id, a.name, a.experience " .
             "FROM users a " .
             "LEFT JOIN users_old USING(user_id, world_id) " .
             "WHERE isNull(users_old.user_id)";
$result = error_query($sql_query, $db);
if ($new = mysql_fetch_assoc($result)) {
    $i = 0;
    $recovered_i = 0;
    $insert_query = "INSERT INTO users_news (user_id, world_id, name, created_at) VALUES ";
    $sql_query_news = $insert_query;
    
    do {
        // check if this is a recovered user
        $sql_query = "SELECT name as name_delete ".
                     "FROM users_deletes ".
                     "WHERE users_deletes.user_id = '" . $new['user_id'] . "' ".
                     "AND users_deletes.world_id = '" . $new['world_id'] . "' ".
                     "ORDER BY users_deletes.created_at DESC LIMIT 1";
        $result_old = error_query($sql_query, $db, true);
        
        #echo "$sql_query\n";
        $recovered = false;
        if ($old = mysql_fetch_assoc($result_old)) {
            if ($new['name'] == $old['name_delete']) {
                // get register date
                $sql_query = "SELECT created_at as datetime FROM users_news " .
                             "WHERE user_id = '{$new['user_id']}' AND world_id = '{$new['world_id']}' " .
                             "ORDER BY created_at DESC LIMIT 1";
                $register_result = mysql_query($sql_query, $db);
                # defaults to beginning of time
                if (!$register = mysql_fetch_assoc($register_result)) { 
                    $register = ['datetime' => strftime("%Y-%m-%d %T", 0)];
                }
                             
                $recovered = true;
                
                echo "recovered: from {$register['datetime']}\n";
                print_r($old);
                print_r($new);
                
                // changes, die seit dem anmeldedatum gemacht wurden,
                // als wiederhergestellt markieren                
                for ($j = 0; $j < $mark_tables_count; ++$j) {
                    implode_runtime($sql_queries[$j], $recovered_i, $update_queries[$j], $db, " OR ");
                    
                    $sql_queries[$j] .= "(user_id = '" . $new['user_id'] . "' ".
                                        "AND world_id = '" . $new['world_id'] . "' " .
                                        "AND created_at >= '" . $register['datetime'] . "')";
                }
                
                ++$recovered_i;
                
                // letztes Anmeldedatum wiederherstellen
                $sql_query = "UPDATE users_news SET deleted = '0' WHERE ".
                             "user_id = '" . $new['user_id'] . "' AND world_id = '" . $new['world_id'] . "'".
                             " AND deleted = '1' ORDER BY created_at DESC LIMIT 1";
                error_query($sql_query, $db);
            }
        }
        
        implode_runtime($sql_query_news, $i, $insert_query, $db);
        $sql_query_news .= "('" . $new['user_id'] . "', '" . $new['world_id'] . "'," . 
                           " '" . mysql_real_escape_string($new['name'], $db) . "', '$now')";
        $i += 1;
    } while ($new = mysql_fetch_assoc($result));
    
    #print_r($sql_queries);
    
    // remaining package
    if ($sql_query_news !== $insert_query) { 
        error_query($sql_query_news, $db);
    }
    
    for ($j = 0; $j < $mark_tables_count; ++$j) {
        if ($sql_queries[$j] !== $update_queries[$j]) { 
            error_query($sql_queries[$j], $db);
        }
    }
    
    echo "$i new Users\n";
} else {
    echo "no new Users\n";
}

for ($j = 0; $j < $mark_tables_count; ++$j) {
    $update_queries[$j] = str_replace("deleted = '0'", "deleted = '1'", $update_queries[$j]);
    $sql_queries[$j] = $update_queries[$j];
}

// Anmeldedatum hier löschen
$update_queries[$mark_tables_count] = "UPDATE users_news SET deleted = '1' WHERE ";
$sql_queries[$mark_tables_count] = $update_queries[$mark_tables_count];
++$mark_tables_count;

// deleted Users
// select from users_old where is no corespondending in users
$sql_query = "SELECT a.user_id, a.world_id, a.name " .
             "FROM users_old a " .
             "LEFT JOIN users USING(user_id, world_id) " .
             "WHERE isNull(users.user_id)";
$result = error_query($sql_query, $db);
if ($delete = mysql_fetch_assoc($result)) {
    $i = 0;
    $insert_query = "INSERT INTO users_deletes (user_id, world_id, name, created_at) VALUES ";
    
    do {
        implode_runtime($sql_query, $i, $insert_query, $db);

        // Löscheintrag
        $sql_query .= "('" . $delete['user_id'] . "', '" . $delete['world_id'] . "'," . 
                      " '" . mysql_real_escape_string($delete['name'], $db) . "', '$now')";
        
        for ($j = 0; $j < $mark_tables_count; ++$j) {
            implode_runtime($sql_queries[$j], $i, $update_queries[$j], $db, " OR ");

            $sql_queries[$j] .= "(user_id = '" . $delete['user_id'] . "' ".
                                "AND world_id = '" . $delete['world_id'] . "')";
        }
        
        $i += 1;
    } while ($delete = mysql_fetch_assoc($result));
    
    #print_r($sql_queries);
    
    // remaining package
    if ($sql_query !== $insert_query) { 
        error_query($sql_query, $db);
    }
    for ($j = 0; $j < $mark_tables_count; ++$j) {
        if ($sql_queries[$j] !== $update_queries[$j]) { 
            error_query($sql_queries[$j], $db);
        }
    }
    
    echo "$i deleted Users\n";
} else {
    echo "no deleted Users\n";
}

// Experience changes
$sql_query = "SELECT users.user_id, users.world_id, users.experience " .
             "FROM users, users_old " .
             "WHERE users.user_id = users_old.user_id AND
                    users.world_id = users_old.world_id AND
                    users.experience != users_old.experience";
$result = error_query($sql_query, $db);
if ($diff = mysql_fetch_assoc($result)) {
    $i = 0;
    $insert_query = "INSERT INTO users_experience_changes (user_id, world_id, experience, created_at) VALUES ";
    
    do {
        implode_runtime($sql_query, $i, $insert_query, $db);       
        
        $sql_query .= "('" . $diff['user_id'] . "', '" . $diff['world_id'] . "'," . 
                     " '" . max(0, $diff['experience']) . "', '$now')";
        
        $i += 1;
    } while ($diff = mysql_fetch_assoc($result));
    
    if ($sql_query !== $insert_query) { // remaining package
        error_query($sql_query, $db);
    }
    
    echo "$i experience-changes\n";
} else {
    echo "no experience-changes\n";
}

// other changes
$changes = array(
    array('column' => 'race_id', 'table' => 'race_changes'),
    array('column' => 'name', 'table' => 'name_changes')
);
foreach ($changes AS $type => $change) {
    $sql_query = "SELECT users.user_id, users.world_id, ".
                 "       users." . $change['column'] . " AS new, users_old." . $change['column'] . " AS old " .
                 "FROM users, users_old " .
                 "WHERE users.user_id = users_old.user_id AND
                        users.world_id = users_old.world_id AND
                        users." . $change['column'] . " != users_old." . $change['column'];
    $result = error_query($sql_query, $db);
    if ($diff = mysql_fetch_assoc($result)) {
        $i = 0;
        $insert_query = "INSERT INTO users_" . $change['table'] . " ".
                        "(user_id, world_id, " . $change['column'] . "_old, " . 
                        $change['column'] . "_new, created_at) VALUES ";

        do {
            implode_runtime($sql_query, $i, $insert_query, $db);       

            $sql_query .= "('" . $diff['user_id'] . "', '" . $diff['world_id'] . "', " . 
                          mysql_encode($diff['old'], $db) . ", " . mysql_encode($diff['new'], $db) . ", ".
                          "'$now')";

            $i += 1;
        } while ($diff = mysql_fetch_assoc($result));

        if ($sql_query !== $insert_query) { // remaining package
            error_query($sql_query, $db);
        }

        echo "$i " . $change['column'] . "-changes\n";
    } else {
        echo "no " . $change['column'] . "-changes\n";
    }
}