<?php
header('Content-Type: text/html; charset=utf-8;');
error_reporting(E_ALL);

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

function get_item_name($name) {
    static $mapped = array(
        
    );
    
    $mapped_name = preg_replace("/^\[\[/", "", strtolower($name));
    
    if (isset($mapped[$mapped_name])) {
        return $mapped[$mapped_name];
    }
    
    return $name;
}

if (isset($_POST['sl_plain'])) {
    $npc_name = "Staubgeist";
    $item_name_mapping = array();
    
    if (preg_match("/(.*)/s", $_POST['sl_plain'], $sl_para)) { // SL
        if (preg_match("/\{\{Gesamtstrichliste\|(.*)\}\}/i", $sl_para[1], $sum_template)) { // Sum-Template
            $sum_parts = explode("|", $sum_template[1]);
            $parts_length = count($sum_parts);
            
            $killcount_sum = $sum_parts[$parts_length-2];
            $slmania_killcount_sum = 0;
            
            #echo '<pre>'.print_r($sum_parts,true).'</pre>';
            
            $items = array();
            for ($i = 1; $i < $parts_length - 2; $i += 2) {
                $name = get_item_name(preg_replace("/^x\s+?\[*(.*?)\]* von\s+?$/i", "$1", $sum_parts[$i]));
                $item_name_mapping[$name] = $sum_parts[$i];
                
                $items_sum[$name] = $sum_parts[$i-1];
                $slmania_items_sum[$name] = 0;
            }
            #echo '<pre>'.print_r($items_sum, true).'</pre>';
            #echo $killcount_sum;
            
            if (preg_match_all("/(\*|#)\s*<!--\s*slmania\s*-->\s*(.*) --/", $sl_para[1], $slmania_entries)) { // Slmania-Entries
                #echo '<pre>'.print_r($slmania_entries, true).'</pre>';
                
                foreach ($slmania_entries[2] as $entry) {
                    if (preg_match("/(.*) (von|bei) (\d+)/i", $entry, $parsed_entry)) {
                        $slmania_killcount_sum += (int)$parsed_entry[3];
                        
                        echo '<pre>'.print_r($parsed_entry, true).'</pre>';
                        foreach (explode(",", $parsed_entry[1]) as $drop) {
                            $parsed_drop = null;
                            
                            // Anzahl Drops und Dropname parsen
                            if (preg_match("/(?P<count>\d+)( (?P<name>.*))?/i", trim($drop), $parsed_drop)) {
                                print_r($parsed_drop);
                                $parsed_drop['count'] = $parsed_drop[0];
                                
                                if (isset($parsed_drop['name'])) {
                                    $parsed_drop['name'] = $parsed_drop['name'];
                                } else {
                                	$tmp_arr = array_keys($items_sum);
                                    $parsed_drop['name'] = $tmp_arr[0];
                                }
                                
                            } else {
                                echo "<p>Couldn't parse drop '$drop'</p>";
                                $parsed_drop = false;
                            }
                            
                            if ($parsed_drop) {
                                $slmania_items_sum[get_item_name($parsed_drop['name'])] += $parsed_drop['count'];
                            }
                        } 
                    } else {
                        echo "<p>Couldn't parse entry '$entry'</p>";
                    }
                }
                
                echo '<pre>'.print_r($items_sum, true).'</pre>';
                echo '<pre>'.print_r($slmania_items_sum, true).'</pre>';
                
                echo '<pre>'.print_r($sum_parts, true).'</pre>';
                
                // create new entry
                // get sum killcount
                $sql_query = "SELECT SUM(killcount) FROM npcs WHERE name = '" . mysql_real_escape_string($npc_name, $db) . "'";
                $tmp_arr = mysql_fetch_row(mysql_query($sql_query, $db));
                $current_killcount = $tmp_arr[0];
                
                // get dropcount
                $sql_query = "SELECT items.name, SUM(items_npcs.count) as count FROM items, npcs, items_npcs ".
                             "WHERE items.name IN ('" . implode("', '", array_keys($items_sum)) . "') ".
                             "AND npcs.name = '" . mysql_real_escape_string($npc_name, $db) . "' ".
                             "AND items.id = items_npcs.item_id AND npcs.id = items_npcs.npc_id ".
                             "GROUP BY items.name";
                $drops = mysql_query($sql_query, $db);
                
                $change = false;
                while ($drop = mysql_fetch_assoc($drops)) {
                    // minus the old entries
                    $drop['count'] -= $slmania_items_sum[$drop['name']];
                    
                    if ($drop['count'] > 0) {
                        $change = true;
                    }
                    
                    // add to sum_template
                    
                    // create entry
                }
                
                $template_string = array("{{Gesamtstrichliste");
                
                for ($i = 0, $i_max = count($items_sum); $i < $i_max; ++$i) {
                	$tmp_arr = array_keys($items_sum);
                    $template_string[] = $items_sum[$tmp_arr[$i]] . "|" . $sum_parts[$i*2 + 1];
                }
                
                $template_string[] = $killcount_sum;
                $template_string[] = $sum_parts[$parts_length-1] . "}}";
                
                echo implode("|", $template_string);
            } else {
                echo "<p>Np SlMania-Entries!</p>";
            }
        } else {
            echo "<p>No Sum-Template found!</p>";
        }
    } else {
        echo "<p>No SL-Paragraph found!/p>";
    }
}
?>
<form action="?" method="POST">
    <input type="submit">
    <textarea style="width: 100%;" rows="30" id="sl_plain" name="sl_plain"><?= htmlspecialchars($_POST['sl_plain']) ?></textarea>
</form>