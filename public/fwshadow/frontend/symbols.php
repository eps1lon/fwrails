<?php
error_reporting(E_ALL ^ E_NOTICE);
$preseed = 1000;
$interval = 260000; //259200;

function rand_blue() {
    return rand(1, 10) == 10; 
}

function rand_yellow() {
    return rand(1, 10) == 10;
}

function rand_green() {
    return rand(1, 10) == 10; 
}

function rand_orange() {
    return rand(1, 10) == 10;
}

$symbols = array(
    0 => 'blue', 
    1 => 'yellow', 
    2 => 'green', 
    3 => 'orange'
);

$position = array(
    'x' => -132,
    'y' => -832
);

?>
<form method="POST" action="?">
    <label for="position_string">Ingame: </label>
    <input type="text" id="position_string" name="position_string"  value="<?= htmlentities($_POST['position_string']); ?>">
    <input type="submit">  
</form>

<form method="POST" action="?">
    <label for="pos_x">X: </label>
    <input type="text" id="pos_x" name="pos_x"  value="<?= (int)$_POST['pos_x']; ?>">
    <label for="pos_y">Y: </label>
    <input type="text" id="pos_y" name="pos_y"  value="<?= (int)$_POST['pos_y']; ?>">
    <input type="submit">  
</form>
<?php

if (isset($_POST['position_string'])) {
    $int = '[0-9\-\.]';
    
    if (preg_match("/Position X: ($int+) Y: ($int+)/", $_POST['position_string'], $matches)) {
        $position['x'] = (int)str_replace('.', '', $matches[1]);
        $position['y'] = (int)str_replace('.', '', $matches[2]);
    }
    
} else if (isset($_POST['pos_x'])) {
    $position = array(
        'x' => (int)$_POST['pos_x'],
        'y' => (int)$_POST['pos_y']
    );
} 

$seed = (integer)($_SERVER['REQUEST_TIME'] / $interval) + $position['x'] + $position['y'] * $preseed;
srand($seed);   

ksort($symbols);

$symbol = "'none'";
foreach ($symbols as $color) {
    $rand = "rand_$color";
      
    if ($rand() === true) {
        $symbol = $color;
    } 
}

echo "Symbol: $symbol" . ' on X: ' . $position['x'] . ' Y: ' . $position['y'].
     ' since ' . cd_string($_SERVER['REQUEST_TIME'] % $interval) . ' changes in '.
     cd_string($interval - $_SERVER['REQUEST_TIME'] % $interval);

/**
 * erzeugt Restzeitanzeige
 *
 * @param int    $diff_time Restzeit
 * @param int    $level     Anzahl angezeigter Zeiteinheiten
 * @param string $separator Verbindung zwischen einzelnen Zeiteinheiten
 * @param array  $parts     Zeiteinheiten mittels [Länge in Sekunden] => array("Bezeichnung", "Plural Suffix")
 * @param int    $flags     Einstellungen für Zeitangabe (1 => Ab erster Zeiteinheit werden auch Zeiteinheiten == 0 eingebunden)
 * @return string
 */
function cd_string($diff_time, $level = 2, $separator = ", ", $parts = NULL, $flags = 0)
{
    if (!$parts)
    {
        $parts = array(
            31536000 => array("Jahr", "en"),
            2592000  => array("Monat", "en"),
            86400    => array("Tag", "en"),
            3600     => array("Stunde", "n"),
            60       => array("Minute", "n"),
            1        => array("Sekunde", "n"),
        );
    }
    $cd_array = array();
    $mod = 0;
    foreach ($parts as $factor => $data)
    {     
        $cd_num = floor((($mod > 0) ? (($diff_time % $mod) / $factor) : ($diff_time / $factor))) ;
        if ($cd_num > 0 || ($flags&1) && count($cd_array) > 0)
        {
            $cd_array[] = $cd_num . " " . $data[0] . (($cd_num > 1) ? $data[1] : "");
        }
        $mod = $factor;
    }
    return implode($separator, array_slice($cd_array, 0, $level));
}