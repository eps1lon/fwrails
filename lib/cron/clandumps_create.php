<?php
require_once 'common.cron.php';

$cols = array(
    'clan_id' => 'clans.clan_id', 
    'name' => 'clans.name', 
    'tag' => 'clans.tag', 
    'leader_id' => 'clans.leader_id', 
    'coleader_id' => 'clans.coleader_id',
    'world' => 'worlds.subdomain'
);
foreach ($cols as $as => &$col) {
    $col = "$col AS $as";
}

$filename = SHARED_PATH . "public/dumps/clans.csv";

$dump = fopen($filename, "w+");

if ($dump !== false) {
    chmod($filename, 0664);

    fputcsv($dump, array_keys($cols), CSV_DELIMITER);
    
    $sql_query = "SELECT " . implode(", ", $cols) . " FROM clans, worlds WHERE ".
                 "worlds.id = clans.world_id ORDER BY clans.world_id, clans.clan_id";
    $clans = error_query($sql_query, $db);
    while ($clan = mysql_fetch_assoc($clans)) {
        fputcsv($dump, array_intersect_key($clan, $cols), CSV_DELIMITER);
    }
}