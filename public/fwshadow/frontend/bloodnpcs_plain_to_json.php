<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
<?php
$unmatched = array();
$bloodnpcs = array();

if (isset($_POST['bloodnpcs_plain'])) {
    if (isset($_POST['bloodnpcs_json'])) {
        $bloodnpcs = json_decode($_POST['bloodnpcs_json'], true);
    }

    foreach (explode("\n", $_POST['bloodnpcs_plain']) as $plain_line) {
        #$unmatched[] = $plain_line;
        
        if (preg_match("/(.*) \((Gruppen|Unique)-NPC\) LP: \d+\/(?P<live>\d+) - Angreifen - Schnellschlag( (?P<strength>\d+))?/", $plain_line, $matches)) {
            echo '<pre>' . print_r($matches, true) . '</pre>';
            
            
            
            $name_parts = explode("-", $matches[1]);
            $prefix = $name_parts[0];
            $npc_name = implode("-", array_slice($name_parts, 1));
            
            $unique_npc = ($matches[2] == "Gruppen") + 1;
            
            $data = array(
                'name' => $npc_name
            );
            
            if (isset($matches['live'])) {
                $data['live'] = (int)$matches['live'];
            }
            
            if (isset($matches['strength'])) {
                $data['strength'] = (int)$matches['strength'];
            }
            
            $data['unique_npc'] = $unique_npc;
            
            $bloodnpcs[$prefix][] = $data;
        } else {
            $unmatched[] = $plain_line;
        }
    }
    
    #echo "<pre>" . print_r($bloodnpcs, true) . "</pre>";
    
    $json = json_encode($bloodnpcs, JSON_UNESCAPED_UNICODE);
    $pretty = str_replace(":[", ": [\n\t", $json);
    $pretty = str_replace("],", "\n],\n", $pretty);
    $pretty = str_replace("},{", "},\n\t{", $pretty);
    
    #echo "<pre>$json</pre>";
    echo "<pre>$pretty</pre>";
    
    $bloodnpcs_json = $_POST['bloodnpcs_json'];
}
?>
<form method="POST">
    <label for="bloodnpcs_plain">Plain Text</label>
    <textarea id="bloodnpcs_plain" name="bloodnpcs_plain" rows="10" cols="70"><?= implode("\n", $unmatched) ?></textarea><br>
    <label for="bloodnpcs_json">NPCs as JSON</label>
    <textarea id="bloodnpcs_json" name="bloodnpcs_json" rows="10" cols="70"><?= $bloodnpcs_json ?></textarea>
    <input type="submit">
</form>
</body></html>