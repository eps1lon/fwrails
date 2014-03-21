<?php
header('Content-Type: text/plain; charset=utf-8;');
$items = array(
    // Name => Kosten
    '[[Axt der Auftragsmörder]]' => 6500,
    '[[blaue Flamme der Ewigkeit]]' => 7500,
    '[[Dolch der Auftragsmörder]]' => 500,
    '[[Eisenrankenrüstung]]' => 8500,
    '[[eiserne Schwingtrommel]]' => 10,
    '[[großer Energiesplitter]]' => 8,
    '[[Gruppen-Hinzauber]]' => 17,
    '[[grüner Baru-Schleier]]' => 10000,
    '[[Ledereinband]]' => 111,
    '[[riesiger Heiltrank]]' => 5,
    '[[Rotationsschild]]' => 38000,
    '[[Rotkellerschimmel]]' => 20,
    '[[Sockel der Erfahrung]]' => 20000,
    '[[Splitter der Genesung]]' => 5000,
    '[[stark durchschlagende Armbrust]]' => 5000,
    '[[Stolperfalle]]' => 6,
    '[[Verräter: Schutzvernichtung]]' => 5,
);

$services = array(
    // Name => Kosten
    'Vollheilung' => 2,
    'Urlaubsreise' => 15,
    'Fundiertes Wissen' => 75,
    'Erfahrungstraining' => 85,
    'Clan-Energie aufladen' => 100
);

// Anzahl Items pro Tabelle
$chunk_size = 7;

$chunks = array_merge(array_chunk($items, $chunk_size, true), array($services));

echo "== Items ==";
foreach ($chunks as $i => $chunk) {
    if ($i === count($chunks) - 1) {
        echo "\n== Dienstleistungen ==";
    }

    echo "\n{| class='prettytable'\n".
         "|- align='center'\n".
         "| class=\"s1\" | '''Stufe'''\n";
    
    foreach ($chunk as $name => $base) {
        echo "| class='s1' | $name\n";
    }
    
    for ($stage = 0; $stage <= 40; ++$stage) {
        echo "|- align=right\n".
             "| class='s5' | '''$stage'''\n";
        foreach ($chunk as $base) {
             echo "| class='s5' | " . number_format(floor(pow(0.99, $stage) * $base), 0, "", ".") . "\n";
        }
    }
    echo "|}";
}
