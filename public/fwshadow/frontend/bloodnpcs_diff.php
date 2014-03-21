<?php
header('Content-Type: text/html; charset=utf-8;');
define('PATTERN_NPC_NAME', "[äöüßa-z\-]+");
define('PATTERN_PREFIX',   "[äöüßa-z]+");

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

$cache_filename = "../data/wiki_npcs.tmp";

if (isset($_GET['refresh_cache'])) {
    $wiki_plain = file_get_contents("http://www.fwwiki.de/index.php?title=Blutwesenliste/Liste&action=edit");
    file_put_contents($cache_filename, $wiki_npcs);
} 

// get wiki entries
$wiki_plain = file_get_contents($cache_filename);

// extract prefix and npc-name
preg_match_all("/\* (" . PATTERN_PREFIX . ")\-(" . PATTERN_NPC_NAME . ")/i", $wiki_plain, $npcs_plain);

$bloodnpcs = array();

// fill into array
foreach ($npcs_plain[1] as $i => $prefix) {
    if (!isset($bloodnpcs[$prefix])) {
        $bloodnpcs[$prefix] = array();
    }

    $bloodnpcs[$prefix][] = $npcs_plain[2][$i];
}

// get all possible bloodnpcs
$possible_bloodnpcs = array();
$sql_query = "SELECT name FROM npcs WHERE pos_x > 0 AND unique_npc = '0' AND name NOT LIKE '% %' GROUP BY name ORDER BY name";
$bloodnpcs_result = mysql_query($sql_query, $db) or die(mysql_error());
while ($bloodnpc = mysql_fetch_assoc($bloodnpcs_result)) {
    $possible_bloodnpcs[] = utf8_encode($bloodnpc['name']);
}

#echo '<pre>' . print_r($possible_bloodnpcs, true) . '</pre>';

$show = array_keys($bloodnpcs);
#$show = array('Todes');

$diffs = array();

foreach (array_intersect($show, array_keys($bloodnpcs)) as $prefix) {
    $npcs = $bloodnpcs[$prefix];
    
    $diff = array_values(array_diff($possible_bloodnpcs, $npcs));
    $diffs[] = $diff;
    
    /*
    echo "<p>$prefix</p>";
    echo '<pre style="float: left;">' . print_r($npcs, true) . '</pre>';
    echo '<pre style="float: left;">' . print_r($possible_bloodnpcs, true) . '</pre>';
    echo '<pre style="float: left;">' . print_r($diff, true) . '</pre>';
    echo '<br clear="both">';//*/
    #break;
}

$intersection = $diffs[0];
for ($i = 1, $length = count($diffs); $i < $length; ++$i) {
    $intersection = array_intersect($intersection, $diffs[$i]);
}

echo '<pre>' . print_r($intersection, true) . '</pre>';

?>
<p><a href="?refresh_cache">Refresh Cache</a></p>