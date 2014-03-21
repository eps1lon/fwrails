<?php
header('Content-Type: image/svg+xml');
require_once '../backend/db.php';

$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_set_charset('utf8', $db);
mysql_select_db(DB_NAME, $db);
		
$tile_size = filter_input(INPUT_GET, 'tile_size', FILTER_VALIDATE_INT);
$area_id = filter_input(INPUT_GET, 'area_id', FILTER_VALIDATE_INT);

$where = "area_id = '" . +$area_id . "'";

$sql_query = "SELECT MIN(pos_x) AS min_x, MAX(pos_x) max_x, ".
             "MIN(pos_y) as min_y, MAX(pos_y) max_y FROM places WHERE $where";
$meassures = mysql_fetch_assoc(mysql_query($sql_query, $db));

$width  = $tile_size * ($meassures['max_x'] - $meassures['min_x'] + 1);
$height = $tile_size * ($meassures['max_y'] - $meassures['min_y'] + 1);
echo '<svg version="1.1" baseProfile="tiny" xmlns="http://www.w3.org/2000/svg" ' .
     'xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" ' .
     'width="' . $width . 'px" height="' . $height . 'px" ' .
     'viewBox="0 0 ' . $width . ' ' . $height . '">';

$sql_query = "SELECT * FROM passages WHERE " .
             "from_x BETWEEN {$meassures['min_x']} AND {$meassures['max_x']} AND " .
             "from_y BETWEEN {$meassures['min_y']} AND {$meassures['max_y']} AND " .
             "to_x BETWEEN {$meassures['min_x']} AND {$meassures['max_x']} AND " .
             "to_y BETWEEN {$meassures['min_y']} AND {$meassures['max_y']} ";
$passages = mysql_query($sql_query, $db);
while ($passage = mysql_fetch_assoc($passages)) {
    $x1 = ($passage['from_x'] - $meassures['min_x']) * $tile_size + $tile_size / 2;
    $x2 = ($passage['to_x']   - $meassures['min_x']) * $tile_size + $tile_size / 2;
    $y1 = ($passage['from_y'] - $meassures['min_y']) * $tile_size + $tile_size / 2;
    $y2 = ($passage['to_y']   - $meassures['min_y']) * $tile_size + $tile_size / 2;
    
    srand(array_sum($passage));
    $color = '#' . dechex(rand(0,255)) . dechex(rand(0,255)) . dechex(rand(0,255));
    echo "<path d='M$x1 $y1 L$x2 $y2' style='stroke: $color; stroke-width: 2px;' stroke-dasharray='1%, 1%'/>";
    echo "<circle cx='$x1' cy='$y1' r='3' />";
    echo "<circle cx='$x2' cy='$y2' r='3' />";
}

echo '</svg>';
   