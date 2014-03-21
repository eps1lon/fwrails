<?php
error_reporting(E_ALL);
header('Content-Type: application/json; charset=iso-8859-1');
header('Access-Control-Allow-Origin: *');

$ob = array("msg" => "");

function dues($str)
{
    return ;
}

function shutdown () {
    echo utf8_decode(html_entity_decode(
        preg_replace('/\\\\u([a-f0-9]{4})/i', '&#x$1;', json_encode($GLOBALS['ob'])),
        ENT_QUOTES, 'UTF-8'
    ));
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

$npcs = array();

if (isset($_GET['name'])) {
    $_GET['name'] = mysql_real_escape_string(utf8_decode($_GET['name']), $db);
    $select = "SELECT COUNT(npcs.id) as count, npcs.id, npcs.pos_x, npcs.pos_y, places.name FROM npcs LEFT JOIN places USING(pos_x, pos_y)";
    $group_order = "GROUP BY npcs.pos_y, npcs.pos_x ORDER BY places.area_id, npcs.pos_y, npcs.pos_x";
    
    $sql_query = $select . " WHERE npcs.name = '" . $_GET['name'] . "'" . $group_order;
    $result = mysql_query($sql_query, $db) or die(mysql_error());
    if (!($npc = mysql_fetch_assoc($result)) || $npc['id'] < 0 && strpos($_GET['name'], "-")) {
        if (($i = strpos($_GET['name'], "-"))) { // möglicherweise ein spezielles Blutprobenwesen
            $_GET['name'] = substr($_GET['name'], $i + 1);
            $ob['msg'] = utf8_encode("[" . $_GET['name'] . "]");
            
            $sql_query = $select . " WHERE npcs.name = '" . $_GET['name'] . "'" . $group_order;
            $result = mysql_query($sql_query, $db);
            $npc = mysql_fetch_assoc($result);
        }
    }
    
    if ($npc) {
        do {
            $npcs[] = array('x' => +$npc['pos_x'], 
                            'y' => +$npc['pos_y'], 
                            'name' => utf8_encode($npc['name']),
                            'count' => +$npc['count']);
        } while ($npc = mysql_fetch_assoc($result));
    }
    
}

$ob['npcs'] = $npcs;