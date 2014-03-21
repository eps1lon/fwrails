<?php
header('Content-Type: text/html; charset=iso-8859-1');

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

echo '<a href="?">Zurueck</a>';
if (isset($_POST['places']) && is_array($_POST['places'])) {
    $sql_query = "SELECT COUNT(id) as fieldcount, name, pos_x FROM places ".
                 "WHERE name IN ('" . implode("', '", array_map("mysql_real_escape_string", $_POST['places'])) . "') ".
                 "GROUP BY name, ABS(pos_x) / pos_x ORDER BY fieldcount DESC";
    $places = mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
    $place = mysql_fetch_assoc($places);
    
    if (isset($_POST['area'])) {
        $area = mysql_real_escape_string($_POST['area'], $db);
        $sql_query = "INSERT INTO areas SET name = '" . $area . "'";
        mysql_query($sql_query, $db);
        
        $area_id = mysql_insert_id($db);
        
        if (!$area_id) {
            $sql_query = "SELECT id FROM areas WHERE name = '" . $area . "'";
            $result = mysql_query($sql_query, $db);
            $area = mysql_fetch_assoc($result);
            $area_id = $area['id'];
        }
        
        do {
            $sql_query = "UPDATE places SET area_id = '$area_id' WHERE name = '" . mysql_real_escape_string($place['name'], $db) . "' AND ".
                         "ABS(pos_x) / pos_x = '" . (abs($place['pos_x']) / $place['pos_x']) . "'";
            mysql_query($sql_query, $db);
            echo "<p>$sql_query; #" . mysql_affected_rows($db) . " affected_rows</p>";
            
        } while ($place = mysql_fetch_assoc($places));
        
    } else {
        echo '<form method="POST" action="?">'.
             '<label for="area">Gebiet: </label>'.
             '<input type="text" name="area" value="' . htmlspecialchars($place['name']) . '" />';
        
        foreach ($_POST['places'] AS $place) {
            echo '<p><input type="checkbox" checked="checked" '.
                           'id="place_' . htmlspecialchars($place) . '" '.
                           'name="places[]" value="' . htmlspecialchars($place) . '">'.
                 '<label for="place_' . htmlspecialchars($place) . '">'.htmlspecialchars($place).'</label></p>';
        }
        echo '<input type="submit" /></form>';
     }
} else {
    $sql_query = "SELECT COUNT(id) as fieldcount, name, pos_x, pos_y FROM places WHERE isNULL(area_id) ".
                 "GROUP BY name, ABS(pos_x) / pos_x ORDER BY SUBSTR(name, 0, 1)";
    $areas = mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);

    echo '<form method="POST" action="?"><ul style="float: left">';
    while ($area = mysql_fetch_assoc($areas)) {
        echo '<li><input id="place_' . htmlspecialchars($area['name']) . '" type="checkbox" name="places[]" value="' . htmlspecialchars($area['name']) . '">'.
             '<label for="place_' . htmlspecialchars($area['name']) . '">' . $area['fieldcount'] . 'x ' . $area['name'] . '(Position X: ' . $area['pos_x'] .' Y: ' . $area['pos_y'] .')</label>';
    }
    echo '</ul><input style="position: fixed; top: 40%;" type="submit" /></form>';
}