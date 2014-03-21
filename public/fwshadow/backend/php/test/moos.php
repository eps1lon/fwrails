<?php
error_reporting(E_ALL);

require '../lib/class.Moos.php';

echo '<p>init</p>';
$moos1 = new Moos(1, array(
    'a' => 8,
    'w' => 9,
    'p' => 4,
    'm' => 3
));

$moos2 = new Moos(1, array(
    'a' => 3,
    'w' => 10,
    'p' => 6,
    'm' => 5
));

echo $moos1;
echo $moos2;

// cross
echo '<p>cross</p>';
echo '<pre>' . print_r($moos1->intersect_with($moos2), true) . '</pre>';
echo array_sum($moos1->intersect_with($moos2));
echo '<pre>' . print_r($moos1->cross_with($moos2), true) . '</pre>';
echo $moos1;

// scion
echo '<p>scion</p>';
echo $moos1->scion($changes);
echo '<pre>' . print_r($changes, true) . '</pre>';

$counts = array();
for ($i = -1; $i <= 2; ++$i) {
    $counts[$i] = 0;
}

$probs = $counts;
for ($i = 0; $i < 10000; ++$i) {
    $counts[Rand::trend(-1, 1)]++;
    #$counts[Moos::cross_value_rand()]++;
}
$sum = array_sum($counts);


foreach ($counts as $key => $count) {
    $probs[$key] = $count / $sum;
}

echo "<table>";
foreach ($probs as $num => $prob) {
    echo "<tr><td>$num</td><td>" . number_format($prob, 5, ',', ' ') . "</td></tr>";
}
echo "</table>";