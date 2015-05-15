<?php
$now = date(DATE_RFC3339);

$file = 'npclist.out';

if (($handle = fopen($file, 'r')) !== false) {
    while (($npc = fgetcsv($handle)) !== false) {
        echo "UPDATE npcs SET gold = '{$npc[4]}' WHERE name = '{$npc[0]}';" . PHP_EOL;
    }
}