<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset="utf-8">
        <style type="text/css">
            ol {
                list-style-type: none;
                margin: 0;
                padding: 0;
            }
            
                ol.letters {
                    overflow: hidden;
                    clear: both;
                }
                    nav + ol.letters > li:before  {
                        content: attr(value) ':'
                    }
                    
                    nav + ol.letters > li  {
                        display: inline;
                    }

                ol.npc {
                    margin-left: 1em;
                    overflow: hidden;
                }
                
                    nav ol.letters li {
                        float: left;
                        width: 1em;
                    }
        </style>
    </head>
    <body>
<?php
require_once '../backend/db.php';

$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME, $db);

mysql_set_charset('utf8', $db);

$shadowcreatures = "id < 0 AND name LIKE 'Schattenkreatur %'";
$bloodnpcs = "id < 0 AND unique_npc > 0 AND LOCATE('-', name) > 0";

if (isset($_GET['evaluate'])) {
    mysql_query("SET SESSION group_concat_max_len = 1024 * 1024 * 1024;", $db) or die(mysql_error() . $sql_query);
    
    $groups = array();
    
    if (isset($_GET['name'])) {
        $groups[$_GET['name']] = "name = '" . mysql_real_escape_string($_GET['name'], $db) . "'";
        $blacklist = "0";
        $droplist = "1";
        
        if ($_GET['name'] == 'Abgesandter der Eiswelt') {
            $groups[$_GET['name'] . ' (Oberflaeche)'] = $groups[$_GET['name']] . " AND pos_x > 0";
            $groups[$_GET['name'] . ' (Vorhof)'] = $groups[$_GET['name']] . " AND pos_x < 0";
        }
        
        echo '<a target="__blank" href="http://fwwiki.de/index.php/' . $_GET['name'] . '">Article</a>';
        echo ' | <a target="__blank" href="http://fwwiki.de/index.php/Diskussion:' . $_GET['name'] . '">Talkpage</a>';
    } else if (isset($_GET['kapsel'])) {
        $blacklist = "id <= 0";
        $droplist = "item_id = '3'";
        
        $groups['A3 V3'] = "name = 'Roteiskoralle'";
        $groups['A155 V3'] = "name IN ('" . implode("', '", array_map("utf8_decode", array(
            'Fleckfarbenfisch',
            'Tiefsee-Aal',
            'Röhrenkrebs',
            'dreiköpfige Wasserschlange'
        ))) . "') OR (name = 'Rotzahnhai' AND pos_x < 0)";
        $groups['A155 V175'] = "!(" . $groups['A3 V3'] . ") AND !(" . $groups['A155 V3'] . ") AND id NOT BETWEEN 323 AND 343 AND id NOT IN (633, 865, 1572)";
    } else if (isset($_GET['shadowcreature'])) {
        $groups['Alle'] = $shadowcreatures;
        $groups['NPC'] = $groups['Alle'] . " AND unique_npc = '0'";
        $groups['Unique-NPC'] = $groups['Alle'] . " AND unique_npc = '1'";
        $groups['Gruppen-NPC'] = $groups['Alle'] . " AND unique_npc = '2'";
        
        $blacklist = "0";
        $droplist = "1";
    } else if (isset($_GET['bloodnpcs'])) {
        $groups['Alle'] = $bloodnpcs;
        $groups['Unique-NPC'] = $groups['Alle'] . " AND unique_npc = '1'";
        $groups['Gruppen-NPC'] = $groups['Alle'] . " AND unique_npc = '2'";
        
        $blacklist = "0";
        $droplist = "item_id = '358'";
    }
    
    if (!empty($groups)) {
        $sum = 0;
        
        foreach ($groups as $name => $group) {
            $sql_query = "SELECT GROUP_CONCAT(id SEPARATOR ',') as  ids, SUM(killcount) AS killcount FROM npcs ".
                         "WHERE $group AND !($blacklist)";
            $result = mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
            $npcs = mysql_fetch_assoc($result);
            
            if ($npcs['ids']) {
                $sql_query = "SELECT name, SUM(count) AS count ".
                             "FROM items_npcs, items WHERE npc_id IN (" . $npcs['ids'] . ") ".
                             "AND item_id = items.id AND $droplist ".
                             "AND items_npcs.action = 'kill' GROUP BY item_id ORDER BY items.name";
                $drops = mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
            }
            
            $sum += $npcs['killcount'];
            
            echo '<table><caption>' . (is_numeric($name) ? htmlspecialchars($_GET['npc']) : $name) . 
                 '</caption><tr><th>Item</th><th>Anzahl</th>'.
                 '<th rowspan="' . (mysql_num_rows($drops) + 1) . '">' . $npcs['killcount'] . '</th></tr>';
            
            if ($drop = mysql_fetch_assoc($drops)) {
                do {
                    echo '<tr><td>' . $drop['name'] . '</td><td>' . $drop['count'] . '</td></tr>';
                } while ($drop = mysql_fetch_assoc($drops));
            } else {
                echo '<tr><td colspan="2">keine Drops gefunden</td></tr>';
            }
            
            echo '</table>';
        }
        echo "<p>Summe: $sum</p>";
        echo '<a href="' . htmlspecialchars($_SERVER['SCRIPT_NAME']) . '">Zurueck zur Auswahl</a>';
    }
} else if (isset($_GET['evaluateitem'])) {
    $sql_query = "SELECT npcs.name as npc_name, items.name as item_name, SUM(items_npcs.count) AS count ".
                 "FROM items, items_npcs, npcs WHERE items.name = '" . mysql_real_escape_string($_GET['name'], $db) . "' ".
                 "AND items_npcs.item_id = items.id AND items_npcs.npc_id = npcs.id ".
                 "GROUP BY npcs.name ORDER BY npcs.name";
    $npcs = mysql_query($sql_query, $db) or die(mysql_error() . $sql_query);
    
    echo "<ul>";
    $drop_sum = 0;
    
    while ($drop = mysql_fetch_assoc($npcs)) {
        echo "<li><a href='?evaluate&name=" . htmlspecialchars($drop['npc_name'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1') . "'>". 
             $drop['npc_name'] . "</a> (" . $drop['count'] . ")</li>";
        
        $drop_sum += $drop['count'];
    }
    
    echo "</ul><p>= $drop_sum</p>";
} else if (isset($_GET['evaluatechase'])) {
    $strength_group = isset($_GET['group']) ? (int)$_GET['group'] : 10;

    mysql_query("SET SESSION group_concat_max_len = 1024 * 1024 * 1024;", $db) or die(mysql_error() . $sql_query);
    
    $sql_query = "SELECT GROUP_CONCAT(id SEPARATOR ',') as  ids, SUM(chasecount) AS chasecount, strength ".
                 "FROM npcs WHERE id > 0 GROUP BY FLOOR(strength / $strength_group)";
    $npcs = mysql_query($sql_query, $db);
    
    echo "<table><caption>Perle der Angst</caption>".
         "<tr><th>Stärke</th><th>Perlen</th><th>Verjagungen</th><th>1:X</th></tr>";
    while ($npc = mysql_fetch_assoc($npcs)) {
        $sql_query = "SELECT SUM(count) as count FROM items_npcs WHERE ".
                     "npc_id IN (" . $npc['ids'] . ") AND item_id = '998' AND ".
                     "action = 'chase'";
        $result = mysql_query($sql_query, $db);
        $drops = mysql_fetch_assoc($result);
        
        if ($drops['count'] > 0) {
            echo "<tr><td>" .  floor($npc['strength'] / $strength_group) * $strength_group . 
                 " - " . (floor($npc['strength'] / $strength_group) + 1) * $strength_group . "</td><td>" . (int)$drops['count'] . "</td>".
                 "<td>" . $npc['chasecount'] . "</td><td>1: " . 
                 ($drops['count'] != 0 ? ($npc['chasecount'] / $drops['count']) : 0) . "</td></tr>";
        }
        
    }
} else {
    if (isset($_GET['listitems'])) {
        $sql_query = "SELECT name FROM items GROUP BY name ORDER BY name";
    } else {
        $sql_query = "SELECT name, unique_npc FROM npcs WHERE !($shadowcreatures) AND !($bloodnpcs) GROUP BY name ORDER BY name";
    }
    
    $npcs = mysql_query($sql_query, $db) or die(mysql_error());
    
    echo '<a href="?">NPCs</a>';
    echo '<br><a href="?listitems">Items</a><br><br>';
    
    echo '<a href="?evaluate&kapsel">Seelenkapsel</a>';
    echo '<br><a href="?evaluatechase">Perle der Angst</a>';
    echo '<br><a href="?evaluate&shadowcreature">Schattenkreaturen</a>';
    echo '<br><a href="?evaluate&bloodnpcs">Blutprobenwesen</a>';
    
    $letter = '';
    
    echo '<nav><ol class="letters">';
    for ($i = 97; $i <= 122; ++$i) {
        echo '<li><a href="#letter_' . strtoupper(chr($i)) . '">' . strtoupper(chr($i)) . '</a></li>';
    }
    echo '</ol></nav>';
    
    echo '<ol class="letters">';
    while ($npc = mysql_fetch_assoc($npcs)) {
        
        if (strtoupper($npc['name'][0]) !== $letter) {
            if ($letter !== '') {
                echo "</ol></li>";
            }
            
            $letter = strtoupper($npc['name'][0]);
            echo '<li value="' . $letter . '" value2="' . ord($letter) . '">
                  <a name="letter_' . $letter . '"></a>
                  <ol class="npc">';
        }
        
        $unique = '';
        if (isset($npc['unique_npc'])) {
            switch ($npc['unique_npc']) {
                case 1:
                    $unique = 'Unique-NPC';
                    break;
                case 2:
                    $unique = 'Gruppen-NPC';
                    break;
                default:
                    $unique = 'NPC';
                    break;
            }
        }
        
        echo "<li><a href=\"?evaluate" . (isset($_GET['listitems']) ? "item" : "") . "&name=" . urlencode($npc['name']) . "\">" . 
             $npc['name'] . "</a> ($unique)</li>";
    }
    echo '</ul>';
}
?>
    </body>
</html>
