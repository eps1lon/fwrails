<?php
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

$sql_query = "SELECT npcs.id, npcs.name, places.name as place_name, npcs.pos_x, npcs.pos_y FROM npcs LEFT JOIN places ON npcs.pos_x = places.pos_x AND npcs.pos_y = places.pos_y ".
             "WHERE npcs.id > 0 ORDER BY npcs.id DESC";
$result = mysql_query($sql_query, $db) or die(mysql_error());
$old = mysql_fetch_assoc($result);
$gaps = array();

echo "<ul>";
while ($npc = mysql_fetch_assoc($result)) {
    $gap = $old['id'] - $npc['id'] - 1;
    
    if ($gap > 0) {
        $gaps[] = $gap;
        echo "<li>$gap between <span title='" . htmlspecialchars($old['id']) . "'>" . $old['name'] . "</span> (" . $old['place_name'] . ": " . $old['pos_x'] . "-" . $old['pos_y'] . ") and <span title='" . htmlspecialchars($npc['id']) . "'>" . $npc['name'] . "</span> (" . $npc['place_name'] . ": " . $npc['pos_x'] . "-" . $npc['pos_y'] . ")</li>";
    }
    $old = $npc;
}
echo "</ul>";
echo count($gaps) . " gaps suma sumarum " . array_sum($gaps);