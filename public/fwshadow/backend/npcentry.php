<?php
error_reporting(E_ALL);
header('Content-Type: application/json; charset=iso-8859-1');
header('Access-Control-Allow-Origin: *');

$ob = array("msg" => "");

function shutdown () {
    echo json_encode($GLOBALS['ob']);
}

register_shutdown_function("shutdown");

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
        
if (!$db) {
    $ob['msg'] = "Verbindung fehlgeschlagen";
    exit;
}

mysql_select_db("slmania", $db);
if (!$db) {
    $ob['msg'] = "Verbindung fehlgeschlagen";
    exit;
}

$sql_query = "SELECT id FROM users WHERE authenticity_token = '" . mysql_real_escape_string($_POST['id'], $db) . "'";
$result = mysql_query($sql_query, $db);
if ($user = mysql_fetch_row($result)) { // User stimmt
    print_r($_POST);
    // parse msg
    $npcs = utf8_encode(stripslashes($_POST['npcs']));
    $npcs = json_decode($npcs);
    $length = count($npcs);
    
    if ($length > 0 && isset($_POST['x']) && isset($_POST['y'])) {
        $pos_x = (int)$_POST['x'];
        $pos_y = (int)$_POST['y'];
        
        foreach ($npcs as $id => $npc) {
            $id = (int)$id;

            if ($id > 0) {
                
                $sql_query = "SELECT id FROM npcs WHERE id = '$id'";
                $result = mysql_query($sql_query, $db);
                if ($test = mysql_fetch_row($result)) {
                    $sql_query = "UPDATE npcs SET pos_x = '$pos_x', pos_y = '$pos_y', ".
                                 "live = '" . (int)$npc->live . "', strength = '" . (int)$npc->strength . "', ".
                                 "unique_npc = '" . (int)$npc->unique . "' WHERE id = '$id'";
                    mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
                } else {
                    $npc->name = utf8_decode($npc->name);
                    $sql_query = "INSERT INTO npcs (id, name, live, strength, unique_npc, pos_x, pos_y) VALUES ".
                                 "('$id', '" . $npc->name . "', '" . (int)$npc->live . "', ".
                                 "'" . (int)$npc->strength . "', '" . (int)$npc->unique . "', '$pos_x', '$pos_y')";
                    mysql_query($sql_query, $db);
                }
            }
        }
    }
} else {
    $ob['msg'] = "User konnte nicht authentiziert werden";
}