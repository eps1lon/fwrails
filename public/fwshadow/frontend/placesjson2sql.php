<?php
header('Content-Type: text/plain; charset=utf-8');
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

function area_id($area, $areas) {
    $area = strtolower($area);
    $area = str_replace(array(" (dungeon)", " (ebene 1)", " (ebene 2)", " (ebene 3)"), "", $area);
    
    foreach ($areas as $id => $name) {
        if ($area == $name) {
            return $id;
        }
        $name = preg_replace("/^(der|die|das) /", "", $name);
        $lev = levenshtein($name, $area);
        #echo "$name == $area > $lev\n";
        if ($lev < 2) {
            return $id;
        }
    }
    
    
    return array_search($area, array(
        12 => "gasthaus - der flur",
        58 => "keller",
        127 => "pensal (brennend)",
        84 => "tal der ruinen (alt)"
    ));
}

function utf8_real_decode ($var) {
    return utf8_decode(html_entity_decode(
        preg_replace('/\\\\u([a-f0-9]{4})/i', '&#x$1;', $var),
        ENT_QUOTES, 'UTF-8'
    ));
}

$areas = array();
$sql_query = "SELECT id, name FROM areas";
$result = mysql_query($sql_query, $db);
while ($area = mysql_fetch_assoc($result)) {
    $areas[$area['id']] = strtolower(utf8_encode($area['name']));
}

$json_plain = file_get_contents("../data/places.json");
$places = json_decode($json_plain);

#print_r($places);

$place_count = 0;
foreach ($places as $area => $fields) {
    $area_id = area_id($area, $areas);
    echo "$area_id: $area\n";
    
    
    if ($area_id === false) {
        break;
    }
    
    for ($i = 0, $count = count($fields); $i < $count; ++$i, ++$place_count) {
        
        $fields[$i]->name = utf8_real_decode($fields[$i]->name);
        
        $sql_query = "INSERT IGNORE INTO places (name, gfx, pos_x, pos_y, area_id) VALUES ";
        $sql_query .= "('" . mysql_real_escape_string($fields[$i]->name, $db) . "', ".
                "'" . mysql_real_escape_string($fields[$i]->gfx, $db) . "', ".
                "'" . $fields[$i]->pos_x . "', '" . $fields[$i]->pos_y . "', '$area_id')";
        mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
    }
    
}

echo "place_count: $place_count (" . mysql_affected_rows($db) . " inserted)";