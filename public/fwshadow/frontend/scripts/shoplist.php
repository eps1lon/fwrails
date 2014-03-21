<?php
header('Content-Type: text/plain; charset=utf-8;');
error_reporting(E_ALL ^E_NOTICE);

define('HOST', 'fwwiki.de');

define('TEMPLATE_GET_KEY',   1);
define('TEMPLATE_GET_VALUE', 2);

function get_templates($template, $wiki_text) {
    $pattern = '/\{\{(Vorlage:)?' . preg_quote($template, '/') . '/';
    
    $templates = preg_split($pattern, $wiki_text);
    
    return array_slice($templates, 1);
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

// verwendung in array_filter, prüfung ob seite und nicht etwa unterkat
function is_page($data) {
    return $data['type'] == 'page';
}

// verwendung in array_filter, nur pageid holen, ähnlich array_column
function extract_pageid($a) {
    return $a['pageid'];
}

// seiten in kategorie $name
function get_cm($name) {
    $cm = array();
    // api url
    $url = 'http://' . HOST . '/api.php?action=query&list=categorymembers'.
           '&cmtitle=Kategorie:' . urlencode($name) . '&cmlimit=max'.
           '&cmstartsortkey=0&cmprop=ids|type&format=json';
    
    $continue_token = '';
    
    do {
        if ($continue_token) { // fortsetzungsseite
            $url .= "&cmcontinue=$continue_token";
        }
        
        // holen, parsen
        $response = json_decode(file_get_contents($url), true);
        
        $continue_token = $response['query-continue']['categorymembers']['cmcontinue'];
        $cm = array_merge($cm, array_map('extract_pageid', array_filter($response['query']['categorymembers'], 'is_page')));
    } while ($continue_token);
    
    return $cm;
}

// limit für prop=revisions
$pageids_limit = 50;

// pageids holen, init pages
$pageids = get_cm('Shops');
$pages = array();

// content holen
for ($i = 0, $length = count($pageids); $i < $length; $i += 50) {
    $url = 'http://www.' . HOST . '/api.php?action=query&prop=revisions'.
           '&rvprop=content&format=json&pageids=' . implode('|', array_slice($pageids, $i, $pageids_limit));
           
    $response = json_decode(file_get_contents($url), true);
    $pages = array_merge($pages, $response['query']['pages']);
}

// init shop gruppen
$templates_shop = array(
    'normale' => array(),
    'Quest' => array(),
    '5k' => array()
);

// seiten durchlaufen
foreach ($pages as $page) {
    // layout vorlage extrahieren und parsen
    $shops = get_templates('Shop/Layout', $content = $page['revisions'][0]['*']);
    $shop = parse_template($shops[0]);
    
    // shopname unterschiedlich von seitenname
    if ($shop['Name']) {
        $name = $shop['Name'];
    } else {
        $name = $page['title'];
    }
    
    // template init: Gebiet, Name, X, Y
    $shop_template = '{{/Shop|Gebiet=' . $shop['Gebiet'] . '|Name=' . $page['title']. 
                     '{{!}}' . $name . '|X=' . $shop['X'] . "|Y=" . $shop['Y'];
                     
    // Einkauf
    if ($shop['Einkauf'] == 'none') {
        $shop_template .= '|Kaufen=nein';
    } else {
        $shop_template .= '|Kaufen=ja';
    }
    // Verkauf
    if ($shop['Verkauf'] == 'none') {
        $shop_template .= '|Verkaufen=nein';
    } else {
        $shop_template .= '|Verkaufen=ja';
    }
    // Bündnisse
    if (preg_match('/Bündnis/', $shop['Voraussetzungen'])) {
        $shop_template .= '|Blau=ja';
        $shop_template .= '|Rot=nein';
    } else if (preg_match('/dunklen Zusammenkunft/', $shop['Voraussetzungen'])) {
        $shop_template .= '|Blau=nein';
        $shop_template .= '|Rot=ja';
    } else if (preg_match("/\* ''\[\[Rasse\]\]:'' \[\[Natla-Händler\]\]/", $shop['Voraussetzungen'])) {
        $shop_template .= '|Blau=nein';
        $shop_template .= '|Rot=nein';
    } else {
        $shop_template .= '|Blau=ja';
        $shop_template .= '|Rot=ja';
    }

    // Reparatur
    if ($shop['Reparatur'] == 'none') {
        $shop_template .= '|Rep=nein';
    } else {
        $shop_template .= '|Rep=ja';
    }
    // sicher
    if ($shop['Friedlich'] == 'none') {
        $shop_template .= '|Sicher=nein';
    } else {
        $shop_template .= '|Sicher=ja';
    }
    
    // schließen
    $shop_template .= '}}';
    
    // Questshop? 5k Shop?
    if (isset($shop['Quest']) && $shop['Quest'] != 'none') { 
        $templates_shop['Quest'][] = $shop_template;
    } else if (preg_match('/Mindesterfahrung/', $shop['Voraussetzungen'])) {
        $templates_shop['5k'][] = $shop_template;
    } else {
        $templates_shop['normale'][] = $shop_template;
    }
} 

// abschließende Ausgabe
foreach ($templates_shop as $type => $templates) {
    echo "<!-- $type Shops: -->\n " . implode("\n ", $templates) . "\n";
}