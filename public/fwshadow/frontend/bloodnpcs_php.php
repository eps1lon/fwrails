<?php
header('Content-Type: text/html; charset=iso-8859-1;');
error_reporting(E_ALL);
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

$prefixes = json_decode(file_get_contents("../data/bloodnpcs.json"), true);
$bloodnpcs = array();

foreach ($prefixes as $npcs) {
    $merge_with = array_filter($npcs, function ($v) {
        return is_array($v);
    });
    $bloodnpcs = array_merge($bloodnpcs, array_map(function ($v) {
        $v['name'] = utf8_decode($v['name']);
        return $v;
    }, $merge_with));
}

$npcs = array();
$npcs_multiple = array();

$sql_query = "SELECT name, live, strength FROM npcs ".
             "WHERE pos_x > 0 AND pos_y > 0 AND unique_npc = 0 AND live > 0 AND strength > 0 ".
             "GROUP BY name, live, strength ".
             "HAVING SUM(LENGTH(name) - LENGTH(REPLACE(name, ' ', ''))+1) = 1";
$result = mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
while ($npc = mysql_fetch_assoc($result)) {
    if (isset($npcs[$npc['name']])) {
        $npcs_multiple[] = $npc['name'];
    } else {
        $npcs[$npc['name']] = $npc;
    }
}
#echo "$sql_query<br>";
$npcs_multiple = array_unique($npcs_multiple);

#echo '<pre>' . print_r($npcs, true) . '</pre>';

$live_to_nlive = array();
foreach ($bloodnpcs as $npc) {
    if (!in_array($npc['name'], $npcs_multiple) && isset($npcs[$npc['name']])) {
        #print_r($npc); echo '<br>';
        #print_r($npcs[$npc['name']]);
        
        if (isset($npc['live']) && $npc['live'] > 0) {
            $factor_live = $npc['live'] / $npcs[$npc['name']]['live'];
            $live_to_nlive[ceil($npc['live']/50)][] = $factor_live;
        }
    }
}

ksort($live_to_nlive);
#echo '<pre>' . print_r($live_to_nlive, true) . '</pre>';
echo '<table><tr><th>x</th><th>ymin</th><th>ymax</th></tr>';
foreach ($live_to_nlive as $x => $y) {
    echo "<tr><td>$x</td><td>" . number_format(min($y), 10, ",", " ") . "</td><td>" . number_format(max($y), 10, ",", " ") . "</td></tr>";
}
echo '</table>';