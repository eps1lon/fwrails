<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
            label, input, textarea {
                display: block;
            }
            
            li input, li label {
                display: inline;
            }
            
            #new_npcs {
                width: 30em;
                height: 10em;
            }
            #npcs_as_wiki {
                width: 100%;
                height: 50em;
            }
            
            .hidden {
                display: none;
                overflow: auto;
            }
        </style>
        <script type="text/javascript">
            function toggle(id) {
                var container = document.getElementById(id);
                return container.style.display = container.style.display == "block" ? "none" : "block";
            }
        </script>
    </head>
    <body>
<?php
function array_flatten_sum (Array $array) {
    return array_sum(array_map(function ($array2) {
        return count($array2);
    }, $array));
}

define('PATTERN_NPC_NAME', "[äöüßa-z\-]+");
define('PATTERN_PREFIX',   "[äöüßa-z]+");

$merge_npcs = false;
$get_wiki_npcs = true;

$prefixes = array();

if (isset($_POST['new_npcs'])) {
    $new_npcs = array();
    
    if ($_POST['format'] == "json") {
        foreach (json_decode($_POST['new_npcs'], true) as $prefix => $npcs) {
            $new_npcs[$prefix] = array();
            
            foreach ($npcs as $npc) {
                if (is_array($npc)) {
                    $npc_name = $npc['name'];
                } else {
                    $npc_name = $npc;
                }
                
                $new_npcs[$prefix][] = $npc_name;
            }
        }
        
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
    } else if ($_POST['format'] == 'painting') {
        $new_npcs = array();
        
        $pattern = "/Zeichnung von (" . PATTERN_PREFIX . ")\-(" . PATTERN_NPC_NAME . ")/i";
        preg_match_all($pattern, $_POST['new_npcs'], $npcs);

        foreach ($npcs[1] as $i => $prefix) {
            if (!isset($new_npcs[$prefix])) {
                $new_npcs[$prefix] = array();
            }
            
            $new_npcs[$prefix][] = $npcs[2][$i];
        }
    }
    
    if (count($new_npcs)) {
        $merge_npcs = true;
        $get_wiki_npcs = true;
        
        echo "<p>Erfasst: " . array_flatten_sum($new_npcs) . "</p>";
    }
}

if ($get_wiki_npcs === true) {
    $cache_filename = "../data/wiki_npcs.tmp";

    if ($_POST['cache'] == "true") {
        $wiki_npcs = file_get_contents($cache_filename);
    } else {
        $wiki_npcs = file_get_contents("http://www.fwwiki.de/index.php?title=Blutwesenliste/Liste&action=edit");
        file_put_contents($cache_filename, $wiki_npcs);
    }
    
    #echo $wiki_npcs;
    preg_match_all("/===\s*(" . PATTERN_PREFIX . ") \((\d+)\)===((\n\* (" . PATTERN_NPC_NAME . "))*)/si", $wiki_npcs, $matches);

    #echo "<pre>" . print_r($matches, true) . "</pre>";
    

    foreach ($matches[3] as $i => $npcs_plain) {
        $prefix = $matches[1][$i];

        preg_match_all("/\* " . PATTERN_PREFIX . "\-(" . PATTERN_NPC_NAME . ")/i", $npcs_plain, $npcs);
        #echo "<pre>" . print_r($npcs, true) . "</pre>";

        sort($npcs[1]);

        $prefixes[$prefix] = $npcs[1];
    }

    #echo "<pre>" . print_r(array_keys($prefixes), true) . "</pre>";
}

if ($merge_npcs === true) {
    $added = 0;
    
    echo "<p>Neu: <a href='#' onclick='javascript: toggle(\"new_npcs\");'>Anzeigen/Ausblenden</a></p>".
         "<ul id='new_npcs' class='hidden'>";
    foreach ($new_npcs as $prefix => $npcs) {
        foreach ($npcs as $npc) {
            if (!in_array($npc, $prefixes[$prefix])) {
                $added++;
                $prefixes[$prefix][] = $npc;
                echo "<li>$prefix-$npc</li>";
            }
        }
        
        sort($prefixes[$prefix]);
    }
    echo "</ul>";
    echo "<p>= $added</p>";
}

$npcs_as_wiki = "==" . array_flatten_sum($prefixes) . " mögliche NPC==\n\n";

foreach ($prefixes as $prefix => $npcs) {
    $npcs_as_wiki .= "===$prefix (" . count($npcs) . ")===\n".
                     implode("", array_map(function ($name) use ($prefix) {
                         return "* $prefix-$name\n";
                     }, $npcs)) . "\n";
}
?>
        <form method="POST">
            <label for="new_npcs">Neue NPCs</label>
            <textarea id="new_npcs" name="new_npcs"><?= htmlspecialchars($_POST['new_npcs'])?></textarea>
            <ul id="formats">
                <li>
                    <input id="format_json" name="format" value="json" type="radio"<?php if ($_POST['format'] == "json") echo ' checked="checked"' ?>>
                    <label for="format_json">JSON ({prefix: [{name: "name"}]} und/oder {prefix: ["name"]})</label>
                </li>
                <li>
                    <input id="format_painting" name="format" value="painting" type="radio"<?php if ($_POST['format'] == "painting") echo ' checked="checked"' ?>>
                    <label for="format_painting">Zeichnung von Blutprobenwesen</label>
                </li>
                <li>
                    <input id="format_desks" name="format" value="desks" type="radio"<?php if ($_POST['format'] == "desks") echo ' checked="checked"' ?>>
                    <label for="format_desks">Foliantenpult (Quelltext kopieren)</label>
                </li>
                <li>
                    <input id="format_book" name="format" value="book" type="radio"<?php if ($_POST['format'] == "book") echo ' checked="checked"' ?>>
                    <label for="format_book">Foliant (Anwenden > Ansehen > Quelltext kopieren)</label>
                </li>
            </ul>
            <input type="hidden" name="cache" value="true">
            <input type="submit">
        </form>
        <label for="npcs_as_wiki">Neuer Wiki-Text:</label>
        <textarea id="npcs_as_wiki"><?= $npcs_as_wiki ?></textarea>
    </body>
</html>