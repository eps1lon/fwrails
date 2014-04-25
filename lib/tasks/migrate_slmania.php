<?php
header('Content-Type: text/plain; charset=utf8');
define('RAILS_ENV', 'production');
require_once '../php/rails_const.php';

function slmania_table($name) {
    return table_name("slmania", $name);
}

function fwrails_table($name) {
    return table_name("stellari_freewar3_" . RAILS_ENV, $name);
}

function table_name($database, $table) {
    return implode(".", array_filter(["`$database`", $table]));
}

$now = date("c");

// connection
$db = mysql_connect('localhost', 'root', 'select11');

// down
$tables = "areas, items, items_npcs, items_places, npcs, npcs_members, places, places_nodes";
echo "truncating tables $tables...";

foreach (explode(", ", $tables) as $table) {
    $sql_query = "TRUNCATE " . fwrails_table($table);
    mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
}
echo "done\n";

// up
// areas
echo "migrating areas...";
$sql_query = "INSERT INTO " . fwrails_table("areas") . " (id, name, created_at, updated_at) " .
             "SELECT id, name, '$now', '$now' FROM " . slmania_table("areas");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

// items
echo "migrating items...";
$sql_query = "INSERT INTO " . fwrails_table("items") . " (id, name, created_at, updated_at) " .
             "SELECT id, name, '$now', '$now' FROM " . slmania_table("items");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

// items_npcs
echo "migrating items_npcs...";
$sql_query = "INSERT INTO " . fwrails_table("items_npcs") .
                " (item_id, npc_id, member_id, count, action, created_at, updated_at) " .
             "SELECT item_id, npc_id, '1', count, action+0, '$now', '$now' " .
                "FROM " . slmania_table("items_npcs");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

// items_places
echo "migrating items_places...";
$sql_query = "INSERT INTO " . fwrails_table("items_places") .
                " (item_id, pos_x, pos_y, count, created_at, updated_at) " .
             "SELECT item_id, pos_x, pos_y, '1', '$now', '$now' " .
                "FROM " . slmania_table("items_places");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

// npcs
echo "migrating npcs...";
$sql_query = "INSERT INTO " . fwrails_table("npcs") .
                  " (id, name, strength, live, pos_x, pos_y, unique_npc, flags, created_at, updated_at) " .
             "SELECT id, name, strength, live, pos_x, pos_y, unique_npc + 1, aggressive, '$now', '$now' " .
                "FROM " . slmania_table("npcs");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

// npcs_members
echo "migrating npcs_members...";
$sql_query = "INSERT INTO " . fwrails_table("npcs_members") .
                  " (npc_id, member_id, chasecount, killcount, created_at, updated_at) " .
             "SELECT id, '1', chasecount, killcount, '$now', '$now' " .
                "FROM " . slmania_table("npcs");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

// places
echo "migrating places...";
$sql_query = "INSERT INTO " . fwrails_table("places") .
                  " (name, `desc`, gfx, pos_x, pos_y, flags, area_id, created_at, updated_at) " .
             "SELECT name, `desc`, gfx, pos_x, pos_y, flags+0, area_id, '$now', '$now' " .
                "FROM " . slmania_table("places");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";

echo "migrating places_nodes...";
$sql_query = "INSERT INTO " . fwrails_table("places_nodes") .
                  " (entry_pos_x, entry_pos_y, exit_pos_x, exit_pos_y, via, created_at, updated_at) " .
             "SELECT to_x, to_y, from_x, from_y, via, '$now', '$now' " .
                "FROM " . slmania_table("passages");
mysql_query($sql_query, $db) or die(implode("\n", [mysql_error(), $sql_query]));
echo "done\n";