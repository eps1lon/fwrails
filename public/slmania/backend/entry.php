<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . filter_input(INPUT_SERVER, 'HTTP_ORIGIN'));
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') == 'OPTIONS') {

    if (filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_METHOD')) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }

    if (filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_HEADERS')) {
        header('Access-Control-Allow-Headers: ' - filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_HEADERS'));
    }

    exit(0);
}

error_reporting(E_ALL ^ E_DEPRECATED);
header('Content-Type: application/json; charset=iso-8859-1');
require_once 'db.php';

$ob = array("msg" => "", 
            "what" => array(),
            "plants_logged" => 0,
            "plants_inserted" => 0);
$now = date("c");

function shutdown () {
    echo json_encode($GLOBALS['ob']);
}

function mysql_encode($var, $db) {
    if (is_numeric($var)) {
        return "'" . (int)($var) . "'";
    }
    if (is_null($var)) {
        return 'NULL';
    }
    return "'" . mysql_real_escape_string(utf8_encode($var), $db) . "'";
}

function db_error_handler ($db) {
    echo "mysql-error#" . mysql_errno($db) . ": \n" . 
                 mysql_error($db);
    
    print_r(debug_backtrace());
    
    exit;
}

function error_query($sql_query) {
    global $db;
    
    $result = mysql_query($sql_query, $db);
    if ($result === false) {
        if (mysql_errno($db) == 2006) { // server gone away
            
            /* re-connect
            mysql_close($db);
            $db = db_connect();
            
            return error_query($sql_query, $db);//*/
        }

        db_error_handler($db);   
    }
    return $result;
}

function action_($action) {
    $actions = [
        "kill" => 1,
        "chase" => 2
    ];
    
    if (isset($actions[$action])) {
        return $actions[$action];
    } else {
        return 3;
    }
}

register_shutdown_function("shutdown");

$starttime = microtime(true);

$sql_query = "SELECT id FROM members WHERE authenticity_token = '" . mysql_real_escape_string(filter_input(INPUT_POST, 'id'), $db) . "'";
$result = error_query($sql_query, $db);
if ($user = mysql_fetch_assoc($result)) { // User stimmt
    // parse msg
    $log_utf8 = utf8_encode(filter_input(INPUT_POST, 'log'));
    $log_unfiltered = json_decode($log_utf8);
    $passages = json_decode(utf8_encode(filter_input(INPUT_POST, 'passages')), true);
    $places = json_decode(utf8_encode(filter_input(INPUT_POST, 'places'))); 
    
    // defaults
    if (!is_array($log_unfiltered)) {
        $log = [];
    } else {
        $log = array_filter($log_unfiltered);
    }
    
    if (!is_array($passages)) {
        $passages = [];
    }
    
    if (!is_object($places)) {
        $places = new stdClass();
    }
    
    $length = count($log) + count($places) + count($passages);

    if ($length > 0) {
        $places_changed = 0;
        $inserted = 0;
        $updated = 0;
        $dropcount = 0;
        $items = array(); // Zwischenspeicher für item_name > item_id
        
        foreach ($log as $i => $entry) {
            if (isset($entry->item)) { // pflanze
                $plant = utf8_decode($entry->item);
                
                if (isset($items[$plant])) {
                    $item_id = $items[$plant];
                } else {
                    $sql_query = "SELECT id FROM items WHERE name = '" . mysql_real_escape_string($plant, $db) . "'";
                    $result = mysql_query($sql_query, $db);
                    if ($item = mysql_fetch_assoc($result)) {
                        $item_id = $item['id'];
                    } else {
                        $sql_query = "INSERT INTO items (name, created_at, updated_at) " .
                                        "VALUES ('" . mysql_real_escape_string($plant, $db) . "', '$now', '$now')";
                        mysql_query($sql_query, $db);
                        $item_id = mysql_insert_id($db);
                    }

                    // ID zwischenspeichern
                    $items[$plant] = $item_id;
                }
                
                $sql_query = "INSERT IGNORE INTO items_places (item_id, pos_x, pos_y, count, created_at, updated_at) VALUES ".
                             "('$item_id', '" . +$entry->place->x . "', '" . +$entry->place->y . "', '1', '$now', '$now')";
                mysql_query($sql_query, $db);
                
                $ob['plants_inserted'] += mysql_affected_rows($db);
                $ob['plants_logged'] += 1;
            } else {
                $npc = isset($log[$i]->npc) ? $log[$i]->npc : $log[$i];

                $npc->name = utf8_decode($npc->name);
                $log[$i]->drops = array_map("utf8_decode", $log[$i]->drops);

                $pos_update_on_update = ", pos_x = '-10', pos_y = '-9'";
                $pos_update_on_insert = ", '-10', '-9'";

                if (isset($log[$i]->action) && $log[$i]->action == 'chase') {
                    $killadd = 0;
                    $chaseadd = 1;
                } else {
                    $killadd = 1;
                    $chaseadd = 0;
                }


                if ($log[$i]->id > 0) { // persistented NPC
                    $npc_id = (int)$log[$i]->id;

                    if (isset($npc->place) && is_object($npc->place)) {
                        $pos_update_on_update = ", pos_x = '" . (int)$npc->place->x . "', pos_y = '" . (int)$npc->place->y . "'";
                        $pos_update_on_insert = ", '" . (int)$npc->place->x . "', '" . (int)$npc->place->y . "'";
                    }
                } else { // temporäres NPC
                    $sql_query = "SELECT id FROM npcs WHERE name = '" . mysql_real_escape_string($npc->name, $db) . "'";
                    $result = error_query($sql_query, $db);

                    if ($search = mysql_fetch_assoc($result)) {
                        $npc_id = (int)$search['id'];
                    } else { // NPC noch nicht eingetragen
                        $npc_id = false; 
                    }
                }

                if ($npc_id !== false) { // NPC persistestent und/oder schon eingetragen
                    $sql_query = "UPDATE npcs SET live = '" . (int)$npc->live . "', ".
                                 "strength = '" . (int)$npc->strength . "', ".
                                 "unique_npc = '" . ((int)$npc->unique + 1) . "', ".
                                 "updated_at = '$now' $pos_update_on_update " .
                                 "WHERE id = '$npc_id'";
                    mysql_query($sql_query, $db);

                    $ob['what'][] = "updated: $npc_id (" . utf8_encode($npc->name) . ")";
                }

                if ($npc_id === false || mysql_affected_rows($db) < 1) { // npc existiert noch nicht

                    if ($npc_id === false) { // temp NPC > neue Id suchen
                        $sql_query = "SELECT MIN(id) FROM npcs WHERE id < 0";
                        $result = mysql_query($sql_query, $db);
                        $npc_id = mysql_fetch_row($result)[0] - 1;
                    } 
                    $ob['what'][] = "inserted: $npc_id (" . utf8_encode($npc->name) . ")";
                    $sql_query = "INSERT IGNORE INTO npcs (id, name, live, strength, unique_npc, pos_x, pos_y, created_at, updated_at) VALUES ".
                                 "('" . $npc_id . "', '" . mysql_real_escape_string($npc->name, $db) . "', ".
                                 "'" . (int)$npc->live . "', '" . (int)$npc->strength . "', ".
                                 "'" . (int)$npc->unique . "'$pos_update_on_insert, '$now', '$now')";
                    mysql_query($sql_query, $db) or die(mysql_error());

                    $inserted += mysql_affected_rows($db);
                } else {
                    $updated += 1;
                }
                
                // npcs_members
                $sql_query = "UPDATE npcs_members SET ".
                                "chasecount = chasecount + $chaseadd, " .
                                "killcount = killcount + $killadd, updated_at = '$now' " .
                             "WHERE npc_id = '$npc_id' AND member_id = '{$user['id']}'";
                mysql_query($sql_query, $db) or die(mysql_error());
                if (!mysql_affected_rows($db)) {
                    $sql_query = "INSERT INTO npcs_members ".
                                    "(npc_id, member_id, chasecount, killcount, created_at, updated_at) VALUES (" .
                                    "'$npc_id', '{$user['id']}', '$chaseadd', " .
                                    "'$killadd', '$now', '$now')";
                    mysql_query($sql_query, $db) or die(mysql_error());
                }
                
                

                if (true) {
                    foreach ($log[$i]->drops as $item_name) {
                        if (isset($items[$item_name])) {
                            $item_id = $items[$item_name];
                        } else {
                            $sql_query = "SELECT id FROM items WHERE name = '" . mysql_real_escape_string($item_name, $db) . "'";
                            $result = mysql_query($sql_query, $db);
                            if ($item = mysql_fetch_assoc($result)) {
                                $item_id = $item['id'];
                            } else {
                                $sql_query = "INSERT INTO items (name, created_at, updated_at) " .
                                              "VALUES ('" . mysql_real_escape_string($item_name, $db) . "', '$now', '$now')";
                                mysql_query($sql_query, $db);
                                $item_id = mysql_insert_id($db);
                            }

                            // ID zwischenspeichern
                            $items[$item_name] = $item_id;
                        }

                        $sql_query = "UPDATE items_npcs SET count = count + 1, updated_at = '$now' ".
                                     "WHERE npc_id = '$npc_id' AND item_id = '$item_id' ".
                                     "AND member_id = '{$user['id']}' " .
                                     "AND action = '" . action_($log[$i]->action) . "'";
                        mysql_query($sql_query, $db);

                        if (mysql_affected_rows($db) < 1) { // neue Drop-Beziehung
                            $sql_query = "INSERT INTO items_npcs (item_id, npc_id, member_id, count, action, created_at, updated_at)".
                                         " VALUES ('$item_id', '$npc_id', '{$user['id']}', " .
                                                  "'1', '" . action_($log[$i]->action) . "', " .
                                                  "'$now', '$now')";
                            mysql_query($sql_query, $db);
                        }

                        $dropcount += 1;
                    }
                }
            }
        } 
        
        $place_queries = array();
        foreach ($places as $key => $place) {
            $pos = explode("|", $key);
            
            $pathinfo = explode("/",$place->gfx);
            $gfx = implode("/", array_slice($pathinfo, array_search("images", $pathinfo) + 1));
            
            $place_queries[] = "(" . (int)$pos[0] . ", " . (int)$pos[1] . ", ".
                               "'" . mysql_real_escape_string(utf8_decode($place->name), $db) . "', ".
                               "'" . mysql_real_escape_string($gfx, $db) . "', ".
                               "'" . mysql_real_escape_string(utf8_decode($place->desc), $db) . "', ".
                               "'" . (int)$place->area_id . "')";
            
            
        }
        $sql_query = "REPLACE INTO places (pos_x, pos_y, name, gfx, `desc`, area_id) ".
                     "VALUES " . implode(", ", $place_queries);
        mysql_query($sql_query, $db);
        $places_changed += mysql_affected_rows($db);
        
        $passage_queries = array();
        
        $args = array(
            'from' => array('flags' => FILTER_FORCE_ARRAY),
            'to' => array('flags' => FILTER_FORCE_ARRAY),
            'via' => array('flags' => FILTER_FORCE_ARRAY)
        );
        
        $place_arg = array(
            'x' => array('filter' => FILTER_VALIDATE_INT,
                         'options' => array('default' => -9)),
            'y' => array('filter' => FILTER_VALIDATE_INT,
                         'options' => array('default' => -10))
        );
        
        foreach ($passages as $passage) {
            $data = filter_var_array($passage, $args);
            
            if (!$data['from'] || !$data['to'] || !$data['via']) {
                continue;
            }

            $from = filter_var_array($data['from'], $place_arg);
            $to = filter_var_array($data['to'], $place_arg);
            
            $passage_queries[] = "(" . $from['x'] . ", " . $from['y'] . ", " .
                                  "" . $to['x'] . ", " . $to['y'] . ", " .
                                 "'" . mysql_real_escape_string(json_encode($data['via']), $db) . "')";
            
            
        }
        $sql_query = "REPLACE INTO places_nodes (exit_pos_x, exit_pos_y, entry_pos_x, entry_pos_y, via) ".
                     "VALUES " . implode(", ", $passage_queries);
        mysql_query($sql_query, $db);
        $ob['passages'] = mysql_affected_rows($db);
        
        $ob['msg'] = true;
        $ob['inserted'] = $inserted;
        $ob['updated'] = $updated;
        $ob['drops'] = $dropcount;
        $ob['places_changed'] = $places_changed;
    } else {
        $ob['msg'] = "Keien Einträge übermittelt";
    }    
} else {
    $ob['msg'] = "User konnte nicht authentiziert werden";
}

$ob['runtime'] = microtime(true) - $starttime;
