<?php
$now = date(DATE_RFC3339);

$file = 'maplist.out';

// places table
$places = [];

if (($handle = fopen($file, 'r')) !== false) {
    while (($place = fgetcsv($handle)) !== false) {
        $places[] = $place;
    }
}

// area names
$areas = array_unique(array_column($places, 0));

// area insert values
$area_values = [];
foreach ($areas as $area_name) {
    $area_values[] = "('$area_name', '$now', '$now')";
}

// insert ignore areas
echo "INSERT IGNORE INTO areas (name, created_at, updated_at) VALUES ".
     implode(', ', $area_values) . ";" . PHP_EOL;

// prepare places for sql
$insert_values = [];
$update_area_ids = [];
foreach ($places as $place) {
    //* update
    echo "UPDATE places SET name = '{$place[1]}', ".
                           "gfx = '{$place[5]}', ".
                           "flags = flags | {$place[4]}, ".
                           "area_id = (SELECT id FROM areas WHERE name = '{$place[0]}'), ".
                           "updated_at = '$now' ".
                       "WHERE pos_x = '{$place[2]}' AND pos_y = '{$place[3]}';" . PHP_EOL;
    //*/ 
                      
    // insert
    // name, pos_x, pos_y, gfx, flags, created_at, update_at                        
    $insert_values[] = "('{$place[1]}', '{$place[2]}', '{$place[3]}', ".
                        "'{$place[5]}', '{$place[4]}', ".
                        "'$now', '$now')";
                        
    $update_area_ids[] = "UPDATE places SET area_id = (SELECT id FROM areas WHERE name = '{$place[0]}') ".
                                       "WHERE pos_x = '{$place[2]}' AND pos_y = '{$place[3]}';";
}

// and insert the remaining
echo "INSERT IGNORE INTO places (name, pos_x, pos_y, gfx, flags, created_at, updated_at) VALUES ".
     implode(', ', $insert_values) . ";" . PHP_EOL;
echo implode(PHP_EOL, $update_area_ids);