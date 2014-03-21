<?php
#if (!isset($_GET['sebulba'])) exit;
header('Content-Type: text/html; charset=utf-8;');
?>
<style type="text/css">
    table {
        border-collapse: collapse;
        border-spacing: 0;
    }
    td, th {
        border: 1px solid black;
        border-width: 1px 0px;
    }
    
    .sortval {
        display: none;
    }
    
    .matched_1 {
        background-color: #886A08;
    }
    
    .matched_2 {
        background-color: #868A08;
    }
    
    .matched_3 {
        background-color: #688A08;
    }
    
    .matched_4 {
        background-color: #4B8A08;
    }
    
    .matched, .matched_5 {
        background-color: #298A08;
    }
    
    #chaos {
        width: 99%;
    }
</style>
<script src="../javascripts/lib/jquery.min.js"></script>
<script src="../javascripts/lib/jquery.metadata.js"></script>
<script src="../javascripts/lib/jquery.tablesorter.js"></script>
<script src="../javascripts/lib/jquery.fixedheader.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var h = $('#chaos tr:first').height() + $('#sort-by').height(),
            headers = (function () {
                var headers = {};
                $('#chaos th').each(function (i) {
                    headers[i] = {
                        sorter: 'sortval'
                    }
                });
                
                return headers;
            }()),
            thead = $('#chaos tr:first').clone();
        
        $.tablesorter.addParser({
            // set a unique id 
            id: 'sortval', 
            is: function(s) { 
                // return false so this parser is not auto detected 
                return false; 
            }, 
            format: function(s) {
                s = s.match(/\{(.+?)\}/)[1];
                var numeric = +s;
                return isNaN(numeric) ? s : numeric; 
            }, 
            // set type, either numeric or text 
            type: 'numeric' 
        });
        
        $('#sort-by').change(function () {
            $('#chaos').trigger('update');
        });

        $('#chaos').tableScroll({height: Math.floor($(window).innerHeight() - h * 2)});
        
        $('#chaos').prepend($('<thead/>', {
            html: thead,
            style: 'display: none;'
        }));
        
        $('#chaos').tablesorter({
            debug: false,
            //headers: headers
            textExtraction: function (node) {
                var by = $('#sort-by')[0],
                    numeric = Number.NaN,
                    val = null;
                    
                by = by.options[by.options.selectedIndex].value;
                val = $('.' + by, node).text();
                if (val === '') {
                    val = node.innerHTML.split(', ')[0];
                }
                
                numeric = +val;
                
                return isNaN(numeric) ? val : numeric;
            }
        });
        
        //*
        $('.tablescroll_head:first th').each(function(i) {
            $(this).click(function() {
                var thead = $(this).parents('thead'),
                    order = thead.attr('order');
                
                // toggle
                if (order === undefined || order > 0) {
                    order = 0;
                } else {
                    order = $('#chaos tbody tr').length;
                }
                
                thead.attr('order', order);
                
                $("#chaos").trigger("sorton", [[[i, order]]]); 
                // return false to stop default link action 
                return false; 
            });
        });//*/
    });
</script>
<?php

function median(Array $arr) {
    $n = count($arr);
    
    if ($n === 0) {
        return false;
    }
    
    sort($arr);
    
    if ($n % 2 === 0) {
        return ($arr[($n / 2) - 1] + $arr[$n / 2 ]) / 2;
    }
    return $arr[$n / 2 ];
}

function to_sortkey($str) {
    return str_replace(array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'), 
                       str_split('aouAOU') + array('ss'), ucfirst($str));
}

$lcfirsts = array("Effektiver Diebstahlzauber", "Globaler Geldregen", "Globaler Schutzzauber",
                  "Mächtiger Starreschutz-Zauber", "Mächtiger Wegzauber-Schutz", 
                  "Schwache Lebenserweiterung", "Starker Kampfunfähigkeitszauber",
                  "Starker Schutzzauber", "Starker Tarnzauber");

$chaos_raw = json_decode(file_get_contents("../data/chaos.json"));

switch (json_last_error()) {
    case JSON_ERROR_NONE:
    break;
    case JSON_ERROR_DEPTH:
        echo 'Maximum stack depth exceeded';
    break;
    case JSON_ERROR_STATE_MISMATCH:
        echo 'Underflow or the modes mismatch';
    break;
    case JSON_ERROR_CTRL_CHAR:
        echo 'Unexpected control character found';
    break;
    case JSON_ERROR_SYNTAX:
        echo 'Syntax error, malformed JSON';
    break;
    case JSON_ERROR_UTF8:
        echo 'Malformed UTF-8 characters, possibly incorrectly encoded';
    break;
    default:
        echo 'Unknown error';
    break;
}

$chaos = array();
#echo "<pre>" . print_r($chaos, true) . "</pre>";

$gl_items = array();
$chaos_props = split(" ", "stage pe exposes items items_sum items_diff items_per");
$chaos_props_num = array_diff($chaos_props, split(" ", "exposes items"));

// evaluate raw data
$sum = 0;
foreach ($chaos_raw as $name => $datas) {
    foreach ($chaos_props as $var) {
        $$var = array();
    }
    
    $sum += count($datas);
    
    foreach ($datas as $data) {
        if (is_numeric($data->stage) && $data->stage > 0) {
            $stage[] = $data->stage;
        }
        
        if (is_numeric($data->pe) && $data->pe > 0) {
            $pe[] = $data->pe;
        }
        
        if ($data->expose && !in_array($data->expose, array('?', 'u'))) {
            $exposes[] = $data->expose;
        }
        
        $items_sum[count($items_sum)] = 0;
        $items_diff[] = count(get_object_vars($data->items));
        
        foreach ($data->items as $item_name => $count) {
            if ($item_name) {
                $items[] = $item_name;
            }
            
            if (is_numeric($count) && $count > 0) {
                $items_per[] = $count;
                $items_sum[count($items_sum) - 1] += $count;
            }
        }
    }
    
    $items = array_filter($items, function ($name) {
        return !($name == '?' || preg_match("/dummy/i", $name));
    });
    
    $items_diff = array_filter($items_diff);
    $gl_items = array_merge($gl_items, $items, $exposes);
    
    $chaos[$name] = array();
    foreach ($chaos_props as $var) {
        $chaos[$name][$var] = $$var;
    }
    
    $chaos[$name]['items'] = array_unique($chaos[$name]['items']);
    $chaos[$name]['exposes'] = array_unique($chaos[$name]['exposes']);
    usort($chaos[$name]['items'], 'strcasecmp');
    usort($chaos[$name]['exposes'], 'strcasecmp');
    
}
echo "Datenmenge: $sum<br>";

$gl_items = array_unique($gl_items);
usort($gl_items, "strcasecmp");

#echo "<pre>" . print_r($chaos, true) . "</pre>";
#echo "<pre>" . print_r($gl_items, true) . "</pre>";

// extract seek information
/*
$_POST['seek'] = "Unbekanntes Item aufdecken - Kosten: 1x Baru-Getreide (5)


Benötigte Charakterfähigkeit zur Herstellung: Labortechnik Stufe 13
Benötigte Erfahrungspunkte zur Herstellung: 13000 Punkte
Benötigte Phasenenergie zur Herstellung: 999 Punkte
Es wird benötigt: 5x schwarze Rose , 5x Portalstein 

nsane deckt auf, dass sie das Item Zauber der unsichtbaren Explosion im Chaoslabor herstellen kann.";//*/
if (isset($_POST['seek'])) {
    $seek = array(
        'expose' => null,
        'stage' => null,
        'pe' => null,
        'items' => null,
        'items_per' => null,
        'items_diff' => null
    );
    
    $text = $_POST['seek'];
    
    if (preg_match("/Unbekanntes Item aufdecken - Kosten: 1x ([a-zA-Z\- ]+)/", $text, $matches)) {
        $seek['exposes'][] = trim($matches[1]);
    }
    
    if (preg_match("/Benötigte Charakterfähigkeit zur Herstellung: Labortechnik Stufe (\d+)/", $text, $matches)) {
        $seek['stage'] = +$matches[1];
    }
    
    if (preg_match("/Benötigte Phasenenergie zur Herstellung: ([0-9\.]+) Punkte/", $text, $matches)) {
        $seek['pe'] = +str_replace(".", "", $matches[1]);
    }
    
    if (preg_match("/Es wird benötigt: (.*)/", $text, $matches)) {
        $matched = explode(", ", $matches[1]);
        
        foreach ($matched as $item) {
            if (preg_match("/(\d+)x ([a-zA-Z\- ]+)/", $item, $matches)) {
                $seek['items'][] = trim($matches[2]);
                $seek['items_diff']++;
                $seek['items_per'][] = +$matches[1];
            } 
        }
    }
    
    #print_r($seek);
    if (count(array_filter($seek)) < 6) {
        echo "couldnt extract enough information";
        unset($seek);
    }
} else {
    $_POST['seek'] = '';
}

// seek info set
if (isset($seek)) {
    $match_props_num = array_diff($chaos_props_num, split(" ", "items_sum items_per"));
    $match_props_arr = split(" ", "items exposes");
    
    $match = array();
    
    // compare
    foreach ($chaos as $name => $data) {
        $match[$name] = array(
            'items' => false,
            'expose' => false,
            'stage' => false,
            'pe' => false,
            'items_diff' => false,
            'items_per' => false
        );
        
        foreach ($match_props_arr as $prop) {
            $match[$name][$prop] = (int)(5 * count(array_intersect($seek[$prop], $data[$prop])) / count($seek[$prop]));
        }
        
        foreach ($match_props_num as $prop) {
            if ($seek[$prop] >= min($data[$prop]) && $seek[$prop] <= max($data[$prop])) {
                $match[$name][$prop] = true;
            }
        }

        if (min($seek['items_per']) >= min($data['items_per']) && max($seek['items_per']) <= max($data['items_per'])) {
            $match[$name]['items_per'] = true;
        }
    }
}

$wiki_cols = [
    1 => 'stage',
    2 => 'pe',
    3 => 'items_diff',
    4 => 'items_per'
];

// merge with wiki data
$wiki_raw = file_get_contents("http://www.fwwiki.de/index.php?title=Chaoslabor/Items&action=raw");
preg_match_all("/^\s*\|(.*)$/m", $wiki_raw, $matches);
foreach ($matches[1] as $table_row) {
    $cols = explode("||", $table_row);
    
    $min_row = [];
    $max_row = [];
    
    if (count($cols) >= 5) {
        preg_match("/([^\|\[]+)\]\]/", $cols[0], $name_match);
        $name = ucfirst($name_match[1]);
        
        foreach ($wiki_cols as $col_num => $prop_name) {
            preg_match("/(\d+)(\s*-\s*(\d+))?$/", trim($cols[$col_num]), $prop_match);
            
            $chaos[$name][$prop_name][] = +$prop_match[1];
            $chaos[$name][$prop_name][] = isset($prop_match[3]) ? +$prop_match[3] : +$prop_match[1];
        }
    } 
}

// display data
$wiki = '';
$evaluation = '';

$evaluation .= '<label for="sort-by">Sortierung nach:</label>'.
               '<select id="sort-by">'.
                    '<option value="min">Minimum</option>'.
                    '<option value="max">Maximum</option>'.
                    '<option value="avg">Durchschnitt</option>'.
                    '<option value="med">Median</option>'.
               '</select>';
$evaluation .= '<table id="chaos" class="tablesorter">'.
                    '<thead><tr>'.
                        '<th>Name</th>'.
                        '<th>Stufe</th>'.
                        '<th>PE</th>'.
                        '<th>Items insg.</th>'.
                        '<th>versch. Items</th>'.
                        '<th>Items je</th>'.
                        '<th width="25%">ZutatenItems</th>'.
                        '<th width="25%">AufdeckItems</th>'.
                        (isset($match) ? '<th>num match</th>' : '').
                    '</tr></thead>'.
                    '<tbody>';
foreach ($chaos as $name => $data) {
    $evaluation .= "<tr><td>$name</td>";
    $lcfirst = in_array($name, $lcfirsts) ? lcfirst($name) : $name;
    $wiki_line = array(
        ($lcfirst !== $name ? "{{SortKey|" . to_sortkey($name) . "}}" : "").
        "{{Anker|$lcfirst}}[[$lcfirst]]"
    );
    
    foreach ($chaos_props_num as $prop) {
        $min = @min($data[$prop]);
        $max = @max($data[$prop]);
        
        $evaluation .= '<td class="' . (isset($match[$name][$prop]) && $match[$name][$prop] === true ? 'matched' : '') . '">';
        if ($min === $max) {
            $evaluation .= $min;
            $wiki_line[] = "{{SortKey|{{nts|$min}}}}$min";
        } else {
            $evaluation .= "<span class=\"min\">$min</span> - ".
                           "<span class=\"max\">$max</span><br>".
                           "∅ <span class=\"avg\">" . round(array_sum($data[$prop]) / count($data[$prop])) . "</span><br>".
                           "x<sup>~</sup> <span class=\"med\">" . median($data[$prop]) . "</span>";
            $wiki_line[] = "{{SortKey|{{nts|$max}}}}$min - $max";
        }
        $evaluation .= "</td>";
        
        if ($prop === "items_sum") {
            array_pop($wiki_line);
        }
    }
    $evaluation .= "<td class=\"" . (isset($match[$name]['items']) === true ? "matched_{$match[$name]['items']}" : '') . "\">" . @implode(", ", $data['items']) . "</td>";
    $evaluation .= "<td class=\"" . (isset($match[$name]['exposes']) === true ? "matched_{$match[$name]['exposes']}" : '') . "\">" . @implode(", ", $data['exposes']) . "</td>";
    
    if (isset($match[$name])) {
        $match_num = count(array_filter($match[$name]));
        $evaluation .= "<td>$match_num</td>";
    }
    
    $evaluation .= '</tr>';
    $wiki .= "&nbsp;|-<br>&nbsp;| " . implode(" || ", $wiki_line). "<br>";
}
$evaluation .= "</tbody></table>";
echo $evaluation;
echo $wiki;
#echo '<form method="post" action=""><textarea name="seek">' . htmlspecialchars($_POST['seek']) . '</textarea><input type="submit"></form>';

if (false) {
    $wiki_items = array();
    
    $cat_url = "http://www.fwwiki.de/index.php/Kategorie:Zutaten_f%C3%BCr_das_Chaoslabor";
    while ($cat_url) {
        $item_page = file_get_contents($cat_url);
        
        preg_match_all('/<li><a.*?title="(.*?)">(.*?)<\/a><\/li>/', $item_page, $rows);
        
        foreach ($rows[2] as $i => $item_name) {
            if ($rows[1][$i] !== $item_name) {
                unset($rows[2][$i]);
            }
        }
        
        $wiki_items = array_merge($wiki_items, $rows[2]);
        
        $cat_url = false;
    }
    
    $gl_items = array_map('ucfirst', $gl_items);
    
    echo "<pre>" . print_r(array_diff($wiki_items, $gl_items), true) . "</pre>";
    echo "<pre>" . print_r(array_diff($gl_items, $wiki_items), true) . "</pre>";
    
    #echo "<pre>" . print_r($wiki_items, true) . "</pre>";
    #echo "<pre>" . print_r($gl_items, true) . "</pre>";
}

#echo "<pre>" . print_r($chaos, true) . "</pre>";
#echo "<pre>" . print_r($match, true) . "</pre>";
#echo "<pre>" . print_r($gl_items, true) . "</pre>";
