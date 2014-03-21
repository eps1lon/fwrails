<?php
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require '../backend/php/lib/class.Shop.php';

function array_flatten($array) {
  if (!is_array($array)) {
    return FALSE;
  }
  $result = array();
  foreach ($array as $key => $value) {
    if (is_array($value)) {
      $result = array_merge($result, array_flatten($value));
    }
    else {
      $result[$key] = $value;
    }
  }
  return $result;
}

function flatten_permutations($permutations) {
    $flatten = array_flatten($permutations);
    $slice_length = count($permutations);
    
    $permutations = array();
    
    for ($i = 0, $count = count($flatten); $i < $count; $i += $slice_length) {
        $permutations[] = array_slice($flatten, $i, $slice_length);
    }
    
    return $permutations;
}

function fakul($i) {
    $fakul = $i;
    for (--$i; $i > 1; $i--) {
        $fakul *= $i;
    }
    return $fakul;
}

function perm($pool,$result=array())
{
    $perms = array();
    if (empty($pool)) {
        $perms[] = $result;
    }
    else {
        foreach($pool as $key => $value) {
          $neuerpool    = $pool;
          $neuerresult  = $result;
          $neuerresult[]= $value;
          unset($neuerpool[$key]);
          $perms[] = perm($neuerpool,$neuerresult);
        }
    }

    return $perms;
}

$items = [
    'Amulett der Blubberheilung', 
    'Amulett des maximalen Wissens',
    'Amulett des Sonnentaus',
    'Kette der Raumzeit',
    'Kette der unsäglichen Kraft'
];
$needed = "Amulett des maximalen Wissens";

$perms = flatten_permutations(perm($items));

foreach ($perms as $counter => $perm) {
    $belpha = new Shop(191, 129, 40000, $perm, 1);
    $belpha->set_buyrange(array(85, 2500));
    $belpha->set_sellrange(array(0, 0));
    
    if ($belpha->sellable_items()[0] == $needed) {
        echo "\$items = [\n    '" . implode("',\n    '", $perm) . "'\n]; // $counter\n";
    }
}

#$permutations = permutate($origin);
#print_r($permutations);

#print_r(flatten_permutations($permutations));