<?php
require_once 'common.cron.php';

$cols = array(
    'user_id' => 'users.user_id', 
    'name' => 'users.name', 
    'experience' => 'users.experience', 
    'race_id' => 'users.race_id', 
    'clan_id' => 'users.clan_id',
    'world' => 'worlds.subdomain'
);

foreach ($cols as $as => &$col) {
    $col = "$col AS $as";
}

$filename = SHARED_PATH . "public/dumps/users.csv";

$dump = fopen($filename, "w+");

if ($dump !== false) {
    chmod($filename, 0664);

    fputcsv($dump, array_keys($cols), CSV_DELIMITER);
    
    $sql_query = "SELECT " . implode(", ", $cols) . " FROM users, worlds WHERE ".
                 "worlds.id = users.world_id ORDER BY users.world_id, users.user_id";
    $users = error_query($sql_query, $db);
    while ($user = mysql_fetch_assoc($users)) {
        fputcsv($dump, array_intersect_key($user, $cols), CSV_DELIMITER);
    }
    
    fclose($dump);
} 
