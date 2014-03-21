<?php
require_once 'common.cron.php';

// latest created_at
$sql_query = "SELECT MAX(created_at) as created_at FROM users_achievements";
$result = mysql_query($sql_query, $db);
$latest = mysql_fetch_assoc($result);

$sql_query = "REPLACE INTO worlds_achievements_changes " .
             "(achievement_id, world_id, progress, created_at) " .
             "SELECT achievement_id, world_id, SUM(progress), " .
                    "'" . $latest['created_at'] . "' " .
             "FROM users_achievements GROUP BY achievement_id, world_id";
mysql_query($sql_query, $db);