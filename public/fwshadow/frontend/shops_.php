<?php
header('Content-Type: text/html; charset=utf-8;');
error_reporting(E_ALL);

require '../backend/php/lib/shop_init.php';

$swamp = new Shop(48, 94, 1000000); // 48, 94
$swamp->seed = 1;
$swamp->set_buyrange(array(1 * 100, 50000 * 100));
$swamp->set_sellrange($swamp->get_buyrange());

$quary_interval = 20000; // 15
$quaries = array(
    new Shop(90, 91, $quary_interval),
    new Shop(87, 91, $quary_interval),
    new Shop(89, 92, $quary_interval),
    new Shop(-1296, -1394, $quary_interval),
    new Shop(-581, -492, $quary_interval)
);

foreach ($quaries as $quary) {
    $quary->seed = 1;
    $quary->set_buyrange(array(1 * 100, 120 * 100));
    $quary->disable_selling();
}

/*
$prices = array(
    1334072710 => 104
);

for ($swamp->seed = 1; $swamp->seed < 1000000; $swamp->seed++) {
    foreach ($prices as $timestamp => $price) {
        srand((integer)($timestamp / 1000000) * $swamp->seed);
        if (rand(1, 50000) == $price) {
            echo "{$swamp->seed} hits $price on $timestamp<br>";
        } else {
            break;
        }
    }
    
}//*/

$abilities = range(1, 44);
/*
$abilities = [
    ''
];//*/

##
# 41 > Phaseneffizienz
##

$shrine = new Shop(108, 77, 250000, $abilities, 1);
//$shrine = new Shop(108, 77, 250000, $abilities, 1);
#$shrine->seed = 0;

echo "Schrein: ";
for ($i = 0; $i < 2; ++$i) {
    $shrine->forecast_intervall($i);
    $shrine->srand();
    shuffle($abilities);
    $ability = array_slice($abilities, 0, 1);
    
    echo $ability[0] . " ->";
}

//
echo 'Shopwechsel: ' . cd_string(end($shops)->changes_in(), 2, ', ') . '<br>';

$forecasts = array();
$i = 0;
Shop::sortByProp($shops, 'sellfactor');
foreach (array_reverse($shops, true) as $name => $shop) {
    $forecasts['sell'][$name] = $shop->lookfor_sellfactor(1.15);
    $forecasts['buy'][$name] = $shop->lookfor_buyfactor(0.85);
    
    if ($i < 2) {
        echo "$name: {$shop->sellfactor()}; nächster 15er <em>" . date("d.m.  h:i", $forecasts['sell'][$name]) . "</em><br>";
    }

    ++$i;
}
echo "<br>";

asort($forecasts['sell']);
asort($forecasts['buy']);

echo "nächste 15er: <br>";
foreach (array_slice(array_filter($forecasts['sell']), 1, 3) as $name => $time) {
    echo "$name: " . date("d.m. h:i", $time) . "<br>";
}
echo "<br>";

echo "nächster Min-Preise:<br>";
foreach (array_slice(array_filter($forecasts['buy']), 1) as $name => $time) {
    echo "$name: " . date("d.m. h:i", $time) . "<br>";
}
echo "<br>";

#echo "Blutechsen für " . (int)(27 * $shops['Blatenien - Gewebezucht']->buyfactor()) . " ({$shops['Blatenien - Gewebezucht']->buyfactor()})<br>";
#echo "Baru-Papier für " . (int)(500 * $mill->buyfactor()) . "<br>";
echo "Belpha: ";
for ($i = 0; $i < 3; ++$i) {
    $belpha->forecast_intervall($i);
    $sellable_items = $belpha->sellable_items();
    echo $belpha->buyfactor() . " " . $sellable_items[0] . 
         " <strong title=\"" . cd_string($belpha->changes_in() + $i * $belpha->get_interval(), 2) . "\">-></strong> ";
}
echo "<br>";
foreach ($belpha->get_items() as $item_name) {
    echo "nächstes $item_name: " . date("d.m h:i", $belpha->lookfor_item($item_name)) . "<br>";
}
echo "<br>";

echo "Plunderladen: $plunder<br>";

echo "Zubehörladen: $laree<br>";
echo "nächstes Schleifpulver: " . date("d.m h:i", $laree->lookfor_item("Schleifpulver")) . "<br>";
echo "nächstes Portalfeldgenerator: " . date("d.m h:i", $laree->lookfor_item("Portalfeldgenerator")) . "<br>";

echo "Nebelsumpf: $swamp<br>";

echo "Steinbrüche (" . cd_string($quaries[0]->changes_in()) . "): ";
foreach ($quaries as $quary) {
    echo "<br>" . $quary->x . "/" . $quary->y . ": " . ($quary->buyfactor() * 0.5);
}

