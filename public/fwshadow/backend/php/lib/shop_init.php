<?php
require 'class.Shop.php';

$shops = array(
    'Ferdolien - Blumenpavillon' => new Shop(106, 95),
    'Konlir - Blumenladen' => new Shop(97, 102),
    'Felsenshop' => new Shop(87, 97),
    'Sutranien - Feuerstelle' => new Shop(71, 90),
    'Konlir - Gebrauchtwarenladen' => new Shop(97, 101),
    'Blatenien - Gewebewurmzucht' => new Shop(86, 107),
    'Narubia - Haus der Finsternis' => new Shop(501, 54),
    'Plefir - Haus der Pilze' => new Shop(79, 110),
    'Ruward - Haus des Friedhods' => new Shop(74, 107),
    'Reikan - Haus des Wissens' => new Shop(98, 111),
    'Terasi - Jägerlager' => new Shop(65, 112),
    'Kerdis - knorriger Baum' => new Shop(90, 115),
    'Mentoran - Nomadendorf' => new Shop(101, 116),
    'Urdanien - Nordhütte' => new Shop(75, 86),
    'Platz der dunklen Zauber' => new Shop(92, 108),
    'Gobos - rote Hütte' => new Shop(77, 101),
    'Das runde Haus der Haushaltswaren' => new Shop(96, 102),
    'Orewu - Salzshop' => new Shop(114, 114),
    'Schlammhaus' => new Shop(122, 91),
    'Torihn - Steindach' => new Shop(112, 91),
    'Buran - Shop' => new Shop(86, 85),
    'Diebeshöhle' => new Shop(-936, -556),   
    'Shop (Tal der Ruinen)' => new Shop(91, 95),
    'Shop der DM' => new Shop(83, 94),
    'Anatubien - Shop der Onlos' => new Shop(90, 100),
    'Reikan - Shop der Serumgeister' => new Shop(94, 109),
    'Mentoran	Shop der Taruner' => new Shop(101, 118),
    'Anatubien - Shop der Tränke' => new Shop(90, 102),
    'Konlir - Shop der Zauberer' => new Shop(102, 103),
    'Nawor - Stachelpflanze' => new Shop(105, 108),
    'Lardikia' => new Shop(120, 115),
    'Die Waffenkammer' => new Shop(99, 99),
    'Kanobien - Waldhaus' => new Shop(71, 101),
    'Loranien - weißes Haus' => new Shop(84, 112),
    'Nawor - Wurzeln' => new Shop(-448, -446),
    'Barumühle' => new Shop(110, 93),
    'Postvogelhaus' => new Shop(109, 100),
    'Taunektarbrauerei' => new Shop(100, 88),
    'Ryn - Torbogen' => new Shop(126, 85)
);

$buys_none = array(
    'Konlir - Gebrauchtwarenladen',
    'Anatubien - Shop der Tränke',
    'Das runde Haus der Haushaltswaren',
    'Platz der dunklen Zauber',
    'Reikan - Haus des Wissens',
    'Die Waffenkammer',
    'Postvogelhaus',
    'Taunektarbrauerei',
    'Shop (Tal der Ruinen)',
    'Schlammhaus',
    'Nawor - Stachelpflanze',
    'Konlir - Blumenladen',
    'Diebeshöhle',
    'Ryn - Torbogen'
);
$sells_none = array('Konlir - Gebrauchtwarenladen');

foreach ($shops as $name => $shop) {
    $shop->name = $name;
    
    if (in_array($name, $buys_none)) {
        $shop->disable_selling();
    }
    if (in_array($name, $sells_none)) {
        $shop->disable_buying();
    }
}

// Belpharia - Unterkunft
$belpha = new Shop(191, 129, 40000, array(
    'Amulett des maximalen Wissens',
    'Amulett der Blubberheilung',
    'Kette der Raumzeit',
    'Kette der unsäglichen Kraft',
    'Amulett des Sonnentaus',
    'Belpharia-Kampfspeer'
), 1);
$belpha->set_buyrange(array(85, 2500));
$belpha->disable_selling();
$belpha->name = 'Belpharia - Unterkunft';

// Plunderladen
$plunder = new Shop(104, 123, 300000, array(
    'Koloa-Schokolade mit Baru-Streuseln',
    'roter Fächer',
    'dunkles Kleid',
    'Krabbentorte',
    'Taschenknaller',
    'Feuerwerkrakete',
    'Largudseife',
    'Hochzeitskleid',
    'Hochzeitsanzug',
    'Pflanzengift',
    'Goldwürfel',
    'Zielscheibe',
    'Itemschleuder',
    'Mondscheinschatulle',
    'Gießkanne'
), 2);
$plunder->disable_selling();
$plunder->name = 'Plunderladen';

// Zubehörladen
$laree = new Shop(54, 77, 6 * 60 * 60, array(
    'Schleifpulver',
    'Fallengift',
    'Schleuderapparatur',
    'Amulettperle',
    'starrende Essenz',
    'Meisterhammer',
    'Weltensplitter',
    'Portalfeldgenerator'
), 2);
$laree->disable_selling();
$laree->name = 'Laree - Zubehör';