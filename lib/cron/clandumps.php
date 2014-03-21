<?php
require 'common.cron.php';

define('MAX_QUERIES', 10000); // max_allowed_pack
define('MAX_CURL_CONNECTIONS', 50);
define('CURL_PROFILE', true);

// other changes
$changes = array(
    array('column' => 'tag', 'table' => 'tag_changes'),
    array('column' => 'name', 'table' => 'name_changes'),
    array('column' => 'leader_id', 'table' => 'leader_changes'),
    array('column' => 'coleader_id', 'table' => 'coleader_changes')
);

$update_queries = array();
$sql_queries = array();
$mark_tables_count = count($changes);

for ($i = 0; $i < $mark_tables_count; ++$i) {
    $update_queries[$i] = "UPDATE clans_" . $changes[$i]['table'] . " SET deleted = '0' WHERE ";
    $sql_queries[$i] = $update_queries[$i];
}

// empty clans_old-table
$sql_query = "TRUNCATE TABLE clans_old";
error_query($sql_query, $db);

// backup clandata for later check of difference
$sql_query = "INSERT INTO clans_old SELECT * FROM clans";
error_query($sql_query, $db);

// empty clans-table
/* just delete the clans from whom we get new data via dumps
$sql_query = "TRUNCATE TABLE clans";
error_query($sql_query, $db);//*/

$skipped_worlds = array();

// loop through each World
$sql_query = "SELECT worlds.id, languages.tld, worlds.subdomain ". 
             "FROM worlds, languages ".
             "WHERE worlds.language_id = languages.id";
$worlds = error_query($sql_query, $db);
while ($world = mysql_fetch_assoc($worlds)) {
    $dump_uri_dest = SHARED_PATH . 'public/dumps/clans/' . $world['subdomain'] . '.txt';
    $dump_uri_src_full = "http://{$world['subdomain']}.freewar.{$world['tld']}/freewar/dump_clans.php";
    $dump_uri_src = "http://{$world['subdomain']}.freewar.{$world['tld']}/freewar/list_clans.php";
    #$dump_uri_src = $dump_uri_dest; // development
   
    
    // get dump
    $dump_full = @trim(file_get_contents($dump_uri_src_full));
    $dump = @trim(file_get_contents($dump_uri_src));
    
    if (!empty($dump_full)) {
        $dump = $dump_full;
    } else if (empty($dump))  {
        // Skipped worlds
        $skipped_worlds[] = $world_id;
        
        echo "empty dump\n";
        continue;
    } 
    // db prep
    $sql_query = "DELETE FROM clans WHERE world_id = '{$world['id']}'";
    error_query($sql_query, $db);
    
    $insert_query = "INSERT IGNORE INTO clans (clan_id, world_id, name, tag, ".
                    "leader_id, coleader_id, sum_experience, member_count, created_at) VALUES ";
    $sql_query = $insert_query;
    
    // xml dump
    $xml = stristr($dump, "<?xml version=\"1.0\"");
    
    if ($xml === false) {
        $lines = explode("\n", $dump);
    } else {
        preg_match_all("/<clan>(.+?)<\/clan>/", $dump, $lines);
        $lines = $lines[1];
    }
    
    // and save it
    file_put_contents($dump_uri_dest, $dump);
    
    if (CURL_PROFILE === true) {
        $profile_master = curl_multi_init();
        $profiles = array();
        $clans = array();
    }

    // loop through each line   
    for ($i = 0, $i_length = count($lines); $i < $i_length; $i += 1) {
        $line = $lines[$i]; // helper
        
        // if we need data from the profile
        $curl_profile = CURL_PROFILE;
        
        if (empty($line)) {
            #echo "empty_line\n";
            continue;
        }               
        $world_id = $world['id'];
        
        $clan = array(
            'world_id' => $world_id,
            'created_at' => $now,
            'name' => '',
            'leader' => 0,
            'coleader' => 0,
            'member' => array(),
            'sum_experience' => 0
        );
        
        if ($xml === false) {
            $data = explode("\t", $line);
            if (empty($data)) {
                continue;
            }
            
            $clan['id'] = (int)$data[0];
            $clan['tag'] = isset($data[1]) ? $data[1] : '';
            
            if (count($data) >= 5) { // extended dump
                $curl_profile = false;
                $clan['name'] = $data[2];
                $clan['leader'] = $data[3];
                $clan['coleader'] = $data[4];
            }
        } else {
            
            $cols = array("id", "tag");
            
            foreach ($cols as $col) {
                preg_match("/<$col>(.*)<\/$col>/i", $line, $matches);
                
                $clan[$col] = $matches[1];
            }
        }
        
        $clans[] = $clan;
        
        if ($curl_profile === true) {
            // look at clan-profile
            $profile_url = "http://" . $world['subdomain'] . ".freewar." . $world['tld'] . 
                           "/freewar/internal/fight.php?action=watchclan&act_clan_id=" . $clan['id'];

            // init curl_handle
            $handle = curl_init($profile_url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_TIMEOUT, 0);

            // and add it
            $profiles[] = $handle;
            curl_multi_add_handle($profile_master, end($profiles));

            if (($i + 1) % MAX_CURL_CONNECTIONS === 0 || $i === $i_length - 1) { // max curl connections
                // exec curl
                do {
                    curl_multi_exec($profile_master, $running);
                } while ($running > 0);

                for($j = 0, $length = count($profiles); $j < $length; $j++)
                {
                    $member_offset = 3;
                    $clan = $clans[$j];
                    $profile = curl_multi_getcontent($profiles[$j]);
                    curl_multi_remove_handle($profile_master, $profiles[$j]);
                    
                    $implode_i = ($i + 1) - MAX_CURL_CONNECTIONS;
                    if ($implode_i < 0) {
                        $implode_i = $j;
                    }
                    
                    implode_runtime($sql_query, $implode_i, $insert_query, $db);

                    preg_match_all('/<p class="maindesc1">(.*)<\/p>/iU', $profile, $matches);

                    $clan['name'] = $matches[1][0];
                    $clan['member_count'] = (int)$matches[1][1];

                    preg_match("/^([0-9\.]* )/i", $matches[1][2], $experience);
                    $clan['sum_experience'] = (int)$experience[1];

                    if (count($matches[1]) > $member_offset &&
                        preg_match("/<b>.*<\/b>(.*)/i", $matches[1][$member_offset], $leader)) {
                        $clan['leader'] = trim($leader[1]);

                        $member_offset++;
                    }
                    if (count($matches[1]) > $member_offset &&
                        preg_match("/<b>.*<\/b>(.*)/i", $matches[1][$member_offset], $coleader)) {
                        $clan['coleader'] = trim($coleader[1]);

                        $member_offset++;
                    }

                    for ($k = $member_offset, $count = count($matches[1]); $k < $count; ++$k) {
                        // extract user_id
                        preg_match("/act_user_id=(\d+)/i", $matches[1][$k], $member);
                        $user_id = (int)$member[1];

                        // extract name for (co-)leader check
                        preg_match("/<a.*>(.*)<\/a>/iU", $matches[1][$k], $member);

                        if (trim($member[1]) == $clan['leader']) {
                            $clan['leader'] = $user_id;
                        } else if (trim($member[1]) == $clan['coleader']) {
                            $clan['coleader'] = $user_id;
                        }

                        $clan['member'][] = $user_id;
                    }

                    if (!empty($clan['member'])) {
                        $update_query = "UPDATE users SET clan_id = '" . $clan['id'] . "' WHERE ".
                                        "user_id IN (" . implode(", ", $clan['member']) . ") ".
                                        "AND world_id = '" . $clan['world_id'] . "'";
                        error_query($update_query, $db);
                    }

                    //clan_id, world_id, name, tag, leader_id, coleader_id, sum_experience, 
                    //member_count, created_at
                    $sql_query .= "(" . mysql_encode($clan['id'], $db) . ", ". 
                                        mysql_encode($clan['world_id'], $db) . ", ".
                                        mysql_encode($clan['name'], $db) . ", ". 
                                        mysql_encode($clan['tag'], $db) . ", ". 
                                        mysql_encode($clan['leader'], $db) . ", ". 
                                        mysql_encode($clan['coleader'], $db) . ", ". 
                                        mysql_encode($clan['sum_experience'], $db) . ", ". 
                                        mysql_encode(count($clan['member']), $db) . ", ". 
                                        mysql_encode($clan['created_at'], $db) . ")";

                    #echo $profile;
                    #print_r($matches);
                    #print_r($clan);

                }

                curl_multi_close($profile_master);
                $profile_master = curl_multi_init();
                $profiles = array();
                $clans = array();
            } 
        } else {
            implode_runtime($sql_query, $i, $insert_query, $db);

            //clan_id, world_id, name, tag, leader_id, coleader_id, sum_experience, 
            //member_count, created_at
            $sql_query .= "(" . mysql_encode($clan['id'], $db) . ", ". 
                                mysql_encode($clan['world_id'], $db) . ", ".
                                mysql_encode($clan['name'], $db) . ", ". 
                                mysql_encode($clan['tag'], $db) . ", ". 
                                mysql_encode($clan['leader'], $db) . ", ". 
                                mysql_encode($clan['coleader'], $db) . ", ". 
                                mysql_encode($clan['sum_experience'], $db) . ", ". 
                                mysql_encode(count($clan['member']), $db) . ", ". 
                                mysql_encode($clan['created_at'], $db) . ")";
        }
        #if ($i) break;
    }
    
    if ($sql_query !== $insert_query) { // remaining package
        error_query($sql_query, $db);
    }
    
    echo "inserted $i clans in " . $world['subdomain'] . "\n";
    #break;
}

echo 'skipped: '; print_r($skipped_worlds);

// set sum/counter cache
$sql_query = "UPDATE clans, " .
                    "(SELECT clans.clan_id, clans.world_id, " .
                            "SUM(experience) as sum_experience, " .
                            "COUNT(users.user_id) as member_count " .
                     "FROM `clans`, users ".
                     "WHERE clans.clan_id = users.clan_id AND " .
                           "clans.world_id = users.world_id " .
                     "GROUP BY clans.clan_id, clans.world_id) cache ".
             "SET clans.sum_experience = cache.sum_experience, " .
                 "clans.member_count = cache.member_count " .
             "WHERE clans.clan_id = cache.clan_id AND " .
                   "clans.world_id = cache.world_id";
error_query($sql_query, $db);

// create dumps
create_dumps($db);

/*
 * recover changes disabled for clans. clans cannot be restored/banned etc
 */

// new clans
// select from clans where is no corespondending in clans_old
$sql_query = "SELECT a.clan_id, a.world_id, a.tag " .
             "FROM clans a " .
             "LEFT JOIN clans_old USING(clan_id, world_id) " .
             "WHERE isNull(clans_old.clan_id)";
$result = error_query($sql_query, $db);
if ($new = mysql_fetch_assoc($result)) {
    $i = 0;
    $insert_query = "INSERT INTO clans_news (clan_id, world_id, tag, created_at) VALUES ";
    
    do {
        implode_runtime($sql_query, $i, $insert_query, $db);
        
        $sql_query .= "('" . $new['clan_id'] . "', '" . $new['world_id'] . "'," . 
                     " '" . mysql_real_escape_string($new['tag'], $db) . "', '$now')";
        
        $i += 1;
    } while ($new = mysql_fetch_assoc($result));
    
    if ($sql_query !== $insert_query) { // remaining package
        error_query($sql_query, $db);
    }
    
    echo "$i new Clans\n";
} else {
    echo "no new Clans\n";
}

for ($j = 0; $j < $mark_tables_count; ++$j) {
    $update_queries[$j] = str_replace("deleted = '0'", "deleted = '1'", $update_queries[$j]);
    $sql_queries[$j] = $update_queries[$j];
}

// deleted clans
// select from clans_old where is no corespondending in clans
$sql_query = "SELECT a.clan_id, a.world_id, a.tag " .
             "FROM clans_old a " .
             "LEFT JOIN clans USING(clan_id, world_id) " .
             "WHERE isNull(clans.clan_id)";
$result = error_query($sql_query, $db);
if ($delete = mysql_fetch_assoc($result)) {
    $i = 0;
    $insert_query = "INSERT INTO clans_deletes (clan_id, world_id, tag, created_at) VALUES ";
    
    do {
        implode_runtime($sql_query, $i, $insert_query, $db);
        
        $sql_query .= "('" . $delete['clan_id'] . "', '" . $delete['world_id'] . "'," . 
                     " '" . mysql_real_escape_string($delete['tag'], $db) . "', '$now')";
        
        // changes als gelöscht markieren
        for ($j = 0; $j < $mark_tables_count; ++$j) {
            implode_runtime($sql_queries[$j], $i, $update_queries[$j], $db, " OR ");

            $sql_queries[$j] .= "(clan_id = '" . $delete['clan_id'] . "' ".
                                "AND world_id = '" . $delete['world_id'] . "')";
        }
        
        $i += 1;
    } while ($delete = mysql_fetch_assoc($result));
    
    #print_r($sql_queries);
    
    if ($sql_query !== $insert_query) { // remaining package
        error_query($sql_query, $db);
    }
    for ($j = 0; $j < $mark_tables_count; ++$j) {
        if ($sql_queries[$j] !== $update_queries[$j]) { 
            error_query($sql_queries[$j], $db);
        }
    }
    
    echo "$i deleted Clans\n";
} else {
    echo "no deleted Clans\n";
}

foreach ($changes AS $change) {
    $sql_query = "SELECT clans.clan_id, clans.world_id, ".
                        "clans." . $change['column'] . " AS new, ".
                        "clans_old." . $change['column'] . " AS old " .
                 "FROM clans, clans_old " .
                 "WHERE clans.clan_id = clans_old.clan_id AND
                        clans.world_id = clans_old.world_id AND
                        clans." . $change['column'] . " != clans_old." . $change['column'];
    $result = error_query($sql_query, $db);
    if ($diff = mysql_fetch_assoc($result)) {
        $i = 0;
        $insert_query = "INSERT INTO clans_" . $change['table'] . " ".
                        "(clan_id, world_id, " . $change['column'] . "_old, " . 
                        $change['column'] . "_new, created_at) VALUES ";

        do {
            implode_runtime($sql_query, $i, $insert_query, $db);       

            $sql_query .= "('" . $diff['clan_id'] . "', '" . $diff['world_id'] . "', " . 
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

// member changes
$changes = array(
    array('column' => 'clan_id', 'table' => 'clan_changes')
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