<?php
require_once 'common.cron.php';

$cols = array(
    'user_id' => 'users_achievements.user_id', 
    'achievement_id' => 'users_achievements.achievement_id', 
    'stage' => 'users_achievements.stage', 
    'world' => 'worlds.subdomain'
);

foreach ($cols as $as => &$col) {
    $col = "$col AS $as";
}

$filename = SHARED_PATH . "public/dumps/achievements.csv";

$dump = fopen($filename, "w+");

if ($dump !== false) {
    chmod($filename, 0664);

    fputcsv($dump, array_keys($cols), CSV_DELIMITER);
    
    $sql_query = "SELECT " . implode(', ', $cols) . " FROM users_achievements, worlds ".
                 "WHERE worlds.id = users_achievements.world_id ".
                 "ORDER BY world_id, user_id";
   $users = error_query($sql_query, $db);
   while ($user = mysql_fetch_assoc($users)) {
       fputcsv($dump, array_intersect_key($user, $cols), CSV_DELIMITER);
   }
}