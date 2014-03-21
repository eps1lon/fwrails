<?php
error_reporting(E_ALL);
header('Content-Type: application/json; charset=iso-8859-1');
header('Access-Control-Allow-Origin: *');

$ob = array("msg" => "");

function shutdown () {
    #echo "<pre>" . print_r($GLOBALS['ob'], true) . "</pre>";
    //*
    echo utf8_decode(html_entity_decode(
        preg_replace('/\\\\u([a-f0-9]{4})/i', '&#x$1;', json_encode($GLOBALS['ob'])),
        ENT_QUOTES, 'UTF-8'
    ));//*/
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
$sql_query = "SELECT *, COUNT(id) as count FROM npcs WHERE 1 GROUP BY name";
$result = mysql_query($sql_query, $db);

$field_num = mysql_num_fields($result);

while ($npc = mysql_fetch_assoc($result)) {
    
    for ($i = 0; $i < $field_num; ++$i) {
        $field_name = mysql_field_name($result, $i);
        switch (mysql_field_type($result, $i)) {
            case "real": // fallthrough for int
            case "int":
                $npc[$field_name] = +$npc[$field_name];
                break;
            default:
                $npc[$field_name] = utf8_encode($npc[$field_name]);
                break;
        }
    }
    
    $sql_query = "SELECT 1 FROM npcs WHERE name = '" . $npc['name'] . "' GROUP BY pos_x > 0, pos_x < 0";
    
    $npc['jumper'] = mysql_num_rows(mysql_query($sql_query, $db)) > 1;
    $npcs[$npc['name']] = $npc;
    
    if ($npc['count'] == 3) {
        #print_r($npc);
    }
    
    #break;
}

$ob['npcs'] = $npcs;