<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
<?php
if (isset($_POST['chaos_plain'])) {
    $item_name = "[0-9ä-üÄ-Üßa-zA-Z\-:, ]+";
    
    switch($_POST['chaos_from']) {
        case 'beri':
            $chaos_stage = 13;
            break;
        case 'ani':
            $chaos_stage = 27;
            break;
        default:
            $chaos_stage = 0;
            break;
    }
    
    $chaos_plain = htmlspecialchars($_POST['chaos_plain'], ENT_COMPAT | ENT_HTML5, 'utf-8');
    
    $text = $_POST['chaos_plain'];
    
    if (preg_match("/Benötigte Charakterfähigkeit zur Herstellung: Labortechnik Stufe (\d+)/", $text, $matches)) {
        $seek['stage'] = +$matches[1];
    }
    
    if (preg_match("/Benötigte Phasenenergie zur Herstellung: ([0-9\.]+) Punkte/", $text, $matches)) {
        $seek['pe'] = +str_replace(".", "", $matches[1]);
        
        $seek['pe'] = ceil($seek['pe'] / pow(0.99, $chaos_stage));
    }
    
    if (preg_match("/Es wird benötigt: (.*)/i", $text, $matches)) {
        $matched = explode(", ", $matches[1]);
        
        foreach ($matched as $item) {
            if (preg_match("/(\d+)x ($item_name)/i", $item, $matches)) {
                $seek['items'][trim($matches[2])] = +$matches[1];
            } 
        }
    }
    
    if (preg_match("/Unbekanntes Item aufdecken - Kosten: 1x ($item_name)/", $text, $matches)) {
        $seek['exposes'] = trim($matches[1]);
    }
    
    echo "<pre>" . print_r($seek, true) . "</pre>";
    echo str_replace(array('\u00e4', '\u00fc', '\u00f6', '\u00df', '\u00c4', '"\u00d6', '\u00dc'), array('ä', 'ü', 'ö', 'ß', 'Ä', 'Ö', 'Ü'), json_encode($seek));
}
?>
<form method="POST">
    <label for="chaos_plain">Plain Text</label>
    <textarea id="chaos_plain" name="chaos_plain" rows="10" cols="70"><?php echo $chaos_plain; ?></textarea>
    <p>hergestellt von</p>
    <label for="chaos_from_me_san">Ich/nsane</label>
    <input id="chaos_from_me_san" type="radio" name="chaos_from" value="me_san"<?php if ($_POST['chaos_from'] == "me_san") echo ' checked="checked"'; ?>>
    <label for="chaos_from_beri">Beri</label>
    <input id="chaos_from_beri" type="radio" name="chaos_from" value="beri"<?php if ($_POST['chaos_from'] == "beri") echo ' checked="checked"'; ?>>
    <label for="chaos_from_ani">Ani</label>
    <input id="chaos_from_ani" type="radio" name="chaos_from" value="ani"<?php if ($_POST['chaos_from'] == "ani") echo ' checked="checked"'; ?>>
    <input type="submit">
</form>
</body></html>