<?php
header('Content-Type: text/plain; charset=utf-8;');
error_reporting(E_ALL ^ E_NOTICE);

define('TEMPLATE_GET_KEY',   1);
define('TEMPLATE_GET_VALUE', 2);

// seiten in kategorie $name
function get_cm($name) {
    $cm = array();
    // api url
    $url = 'http://' . WIKI_HOST . '/api.php?action=query&list=categorymembers'.
           '&cmtitle=Kategorie:' . urlencode($name) . '&cmlimit=max'.
           '&cmstartsortkey=0&cmprop=ids|title|type|sortkeyprefix&format=json&cmsort=sortkey';
    $continue_token = '';
    
    do {
        if ($continue_token) { // fortsetzungsseite
            $url .= "&cmcontinue=$continue_token";
        }
        
        // holen, parsen
        $response = json_decode(file_get_contents($url), true);
        
        $continue_token = @$response['query-continue']['categorymembers']['cmcontinue'];
        $cm = array_merge($cm, array_map('extract_data', array_filter($response['query']['categorymembers'], 'is_page')));
    } while ($continue_token);
    
    return $cm;
}

// filter für get_cm
function is_page($data) {
    return $data['sortkeyprefix'][0] != '!' && 
           $data['type'] == 'page';
}

// map für get_cm
function extract_data($v) {
    return str_replace("Felder:", "", $v['title']);
}

function is_obsolete($wiki_text) {
    // Veraltetes Feld=none liefert true!
    return (bool)preg_match('/Veraltetes Feld=[^}]+/', $wiki_text);
}

function get_templates($template, $wiki_text) {
    $pattern = '/\{\{(Vorlage:)?' . preg_quote($template, '/') . '/';

    $templates = preg_split($pattern, $wiki_text);

    return array_slice($templates, 1);
}

function parse_field_article($area, $host) {
    $fields = array();

    // Artikel fetchen
    $field_url = "$host/index.php/Felder:" . rawurlencode($area) . "?action=raw";
    $html = file_get_contents($field_url);

    if (is_obsolete($html) === true) { // veraltet
        return array();
    } else {
        // Layout Vorlagen matchen
        $field_templates = get_templates('Feldzusammenfassung/Layout', $html);

        // Layout Vorlagen durchlaufen
        foreach ($field_templates as $field_template) {
            // init und parsen
            $field = array_merge(array('area' => $area), parse_field_template($field_template));

            // push
            $fields[] = $field;
        }

        return $fields;
    }
}

function parse_field_template($wiki_text) {
    // Standard-Werte
    $field = array(
        'name'       => '',
        'pos_x'      => -10,
        'pos_y'      => -9,
        'flags'      => 0,
        'url'        => ''
    );

    // Vorlage als Array: Parameter => Wert
    $template = parse_template($wiki_text);

    // Vorlagewerte maschinenlesbar machen
    $field['pos_x'] = (int)$template['X'];
    $field['pos_y'] = (int)$template['Y'];
    $field['url']   = preg_replace('/(.*)\/images\/map\/(.+?)$/', '$2', $template['Bild']);
    $field['name']  = $template['Name'];
    $field['flags'] |= (int)(strpos($template['Friedlich'], 'nein') === false) * pow(2, 1);

    return $field;
}

function parse_template($text) {
    $template = array();

    /* nicht kompatibel mit verschachtelten Vorlagen
    // Key-Value Paare spliten
    $lines = array_filter(explode('|', $template_text));

    foreach ($lines as $line) {
        // Key/Value trennen
        $keyval = explode('=', $line, 2);
        // und entsprechend ins Array eintragen
        $template[$keyval[0]] = trim($keyval[1]); // 'Parameter=' wirft undefined offset 1
    }//*/

    $key = '';
    $mode = TEMPLATE_GET_KEY;
    $depth = 0;

    for ($i = 1, $length = strlen($text); $i < $length; ++$i) {
        if ($text[$i] === '{' && $text[$i+1] === '{') { // weitere Vorlage
            ++$depth;
            ++$i;
            $template[$key] .= '{';
        } else if ($text[$i] === '}' && $text[$i+1] === '}') { // geschlossene Vorlage

            if ($depth === 0) {
                break;
            } else {
                --$depth;
                ++$i;
                $template[$key] .= '}';
            }
        } else if ($text[$i] === '[' && $text[$i+1] === '[') { // geöffneter Link
            ++$depth;
            ++$i;
            $template[$key] .= '[';
        } else if ($text[$i] === ']' && $text[$i+1] === ']') { // geschlossener Link

            if ($depth === 0) {
                break;
            } else {
                --$depth;
                ++$i;
                $template[$key] .= ']';
            }
        }

        if ($text[$i] === '=' && $depth === 0) { // Wertzuweisung beginnt
            $mode = TEMPLATE_GET_VALUE;
            $depth = 0;
            $template[$key] = '';
        } else if ($text[$i] === '|' && $depth === 0) { // Parameter Sparierung
            $mode = TEMPLATE_GET_KEY;
            $key = '';
        } else if ($mode === TEMPLATE_GET_KEY) {
            $key .= $text[$i];
        } else if ($mode === TEMPLATE_GET_VALUE) { // Wert wird geschrieben
            $template[$key] .= $text[$i];
        }
    }

    return array_map('trim',$template);
}

define(WIKI_HOST, 'fwwiki.de');
$host            = 'http://www.' . WIKI_HOST;
$prefix          = 'Felder';                           // Wiki-Namespace
$parser_function = 'parse_field_article';              // Parser Funktion des Skripts
$category_url    = "$host/index.php/Kategorie:Felder"; // Gebietskategorie

// init
$fields = array();

// Gebiete fetchen
$areas = get_cm("Felder");

// durchlaufen
foreach ($areas as $area) {
    $fields = array_merge($fields, $parser_function($area, $host));
}

// und ausgeben
$delimiter = ';';

// head
#echo implode($delimiter, array_keys($fields[0])) . "\n";

// body
/* own csv function
foreach ($fields as $field) {
    echo implode($delimiter, $field) . "\n";
}//*/

// php csv
$out = fopen('php://output', 'w');
foreach ($fields as $field) {
    fputcsv($out, $field);
}
fclose($out);