<?php
    define('TILE_SIZE', 20);
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset="utf-8">
        <style type="text/css">
            #map {
                background: transparent;
                position: relative;
            }
            
            #pos {
                background-color: white;
                border: 1px solid black;
                position: fixed;
                top: 3em;
                z-index: 3;
            }
            
            .place {
                position: absolute;
                z-index: 2;
            }
            
            #passage_graph {
                position: absolute;
                top: 0px;
                left: 0px;
                width: 100%;
                height: 100%;
                z-index: 1;
            }
        </style>
    </head>
    <body>
<?php
require_once '../backend/db.php';

$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_set_charset('utf8', $db);

error_reporting(E_ALL);

$do = filter_input(INPUT_GET, 'do');

if ($do == 'del') {
    mysql_select_db(DB_NAME, $db);
    
    $x = filter_input(INPUT_GET, 'x', FILTER_VALIDATE_INT);
    $y = filter_input(INPUT_GET, 'y', FILTER_VALIDATE_INT);
    
    $sql_query = "DELETE FROM places WHERE pos_x = '$x' AND pos_y = '$y'";
    mysql_query($sql_query, $db);
    echo "<p>deleted: " . mysql_affected_rows($db) . " places</p>";
    
    $sql_query = "DELETE FROM passages WHERE (from_x = '$x' AND from_y = '$y') OR " .
                 "(to_x = '$x' AND to_y = '$y')";
    mysql_query($sql_query, $db);
    echo "<p>deleted: " . mysql_affected_rows($db) . " passages</p>";
}

$area_id = filter_input(INPUT_GET, 'area_id', FILTER_VALIDATE_INT);
if ($area_id) {
		mysql_select_db(DB_NAME, $db);
		
    $where = "area_id = '" . +$area_id . "'";

    $sql_query = "SELECT MIN(pos_x) AS min_x, MAX(pos_x) max_x, ".
                        "MIN(pos_y) as min_y, MAX(pos_y) max_y FROM places WHERE $where";
    $meassures = mysql_fetch_assoc(mysql_query($sql_query, $db));
    
    
    $sql_query = "SELECT COUNT(*) FROM places WHERE $where";
    $size = mysql_fetch_row(mysql_query($sql_query, $db));
    echo "<h1>Groesse: {$size[0]}</h1>";
    echo "<div id='pos'>Position X: <em id='pos_x'></em> Y: <em id='pos_y'></em></div>";
    echo "<div id='map' style='width: " . (TILE_SIZE * ($meassures['max_x'] - $meassures['min_x'] + 1)) . "px;".
         "height: " . (TILE_SIZE * ($meassures['max_y'] - $meassures['min_y'] + 1)) . "px;'>";
    
    echo "<img id='passage_graph' src='map_passages.php?area_id=$area_id&tile_size=" . TILE_SIZE . "'>";
    
    $sql_query = "SELECT pos_x, pos_y, gfx FROM places WHERE $where";
    $places = mysql_query($sql_query, $db);
    while ($place = mysql_fetch_assoc($places)) {
        $gfx = 'http://welt1.freewar.de/freewar/images/' . $place['gfx'];
        echo "<a href='?area_id=$area_id&do=del&x={$place['pos_x']}&y={$place['pos_y']}'>" .
             "<img class='place' src='$gfx' style='width: " . TILE_SIZE . "; ".
             "height: " . TILE_SIZE . "; left: ".
             (TILE_SIZE * ($place['pos_x'] - $meassures['min_x'])) . "px; top: ".
             (TILE_SIZE * ($place['pos_y'] - $meassures['min_y'])) . "px;'".
             " id='mapx{$place['pos_x']}y{$place['pos_y']}' ".
             "onmouseover='javascript: pos({$place['pos_x']}, {$place['pos_y']});'></a>";
    }
    
    echo "</div>";
} else {
    $sql_query = "SELECT " . DB_NAME . ".areas.name as area_name, " . DB_NAME . ".places.name as place_name, ".
                 "stellari_freewar3_production.users.name AS user_name, ".
                 DB_NAME . ".places.area_id FROM " . DB_NAME . ".places ".
                 "LEFT JOIN " . DB_NAME . ".areas ON areas.id = places.area_id ".
                 "LEFT JOIN stellari_freewar3_production.users ON users.user_id = " . DB_NAME . ".places.area_id ".
                 "WHERE stellari_freewar3_production.users.world_id = 3 ".
                 "GROUP BY places.area_id ORDER BY user_name, area_name, places.area_id";
    $areas = mysql_query($sql_query, $db);
    echo mysql_error();
    echo $sql_query;
    
    echo "<ul>";
    while ($area = mysql_fetch_assoc($areas)) {
    		if ($area['area_name']) {
    			  $area_name = $area['area_name'];
    		} else if ($area['user_name']) {
    			  $area_name = $area['user_name'];
    		} else {
    				$area_name = $area['area_id'];
    		}
        $area_name = implode(' | ', array_filter(array($area['user_name'], $area['area_name'], $area['area_id'])));
        echo "<li><a href='?area_id={$area['area_id']}'>$area_name ".
             "({$area['place_name']})</a></li>";
    }
    echo "</ul>";
}


?>
        <script type="text/javascript">
            function pos(x, y) {
                document.getElementById('pos_x').innerHTML = x;
                document.getElementById('pos_y').innerHTML = y;
                return;
            }
        </script>
    </body>
</html>