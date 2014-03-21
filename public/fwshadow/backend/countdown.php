<?php
#if (!isset($_GET['dimmer'])) exit;

error_reporting(E_ALL);

if (isset($_GET['interval'])) {
    $intervals = array($_GET['interval']);
} else {
    
    
    if (isset($_GET['count'])) {
        $count = (int)$_GET['count'];
    } else {
        $count = 1;
    }
    
    if (isset($_GET['diff'])) {
        $diff = (int)$_GET['diff'];
    } else {
        $diff = 1;
    }
    
    if (isset($_GET['offset'])) {
        $offset = (int)$_GET['offset'];
        $offset += $diff % $offset;
        $offset = max($offset, $diff);
    } else {
        $offset = $diff;
    }
    
    $intervals = array_fill($offset / $diff, $count, $diff);
    array_walk($intervals, function (&$value, $key) {
        $value *= $key;
    });
}

$ob = array();

foreach ($intervals AS $interval) {
    $seed = (int)($_SERVER['REQUEST_TIME'] / $interval);
    $since = cd_string(abs(($seed) * $interval - $_SERVER['REQUEST_TIME']), 3);
    $in = cd_string(($seed + 1) * $interval - $_SERVER['REQUEST_TIME'], 3);
    
    $ob[] = array(
        'since' => $since, 
        'in' =>  $in, 
        'interval' => $interval
    );
}

if (!isset($_GET['format'])) {
    $_GET['format'] = '';
}

switch ($_GET['format']) {
    case 'json':
        header("Content-Type: text/plain; charset=utf-8");
        echo json_encode($ob);
        break;
    case 'html':
        header("Content-Type: text/html; charset=utf-8");
        echo "<table border=1><tr><th>Interval</th><th>Since</th><th>In</th><th>sum</th></tr>";
        foreach ($ob as $data) {
            echo "<tr><td>" . $data['interval'] . "</td><td>" . $data['since'] . "</td><td>" . $data['in'] . "</td><td>" . cd_string($data['interval'], 5) . "</td></tr>";
        }
        echo "</table>";
        break;
    default:
        header("Content-Type: text/plain; charset=utf-8");
        print_r($ob);
        break;
}

/**
 * cd_string: Git eine Countdown-Anzeige zurück
 *
 * @param  integer Restzeit in Sekunden
 * @param  integer Gibt an wieviele Zeiteinheiten angzeigt werden
 * @param  string  Trennzeichen zwischen Zeiteinheiten
 * @param  array   falls man eigene Zeiteinheiten verwenden will oder zur Fallunterscheidung
 * @return string
 */
function cd_string($diff_time, $level = 2, $separator = ", ", $parts = NULL)
{
    if (!$parts)
    {
        $parts = array(
            31536000 => array("Jahr", "e"),
            2592000  => array("Monat", "e"),
            86400    => array("Tage", "n"),
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
        if ($cd_num > 0)
        {
            $cd_array[] = $cd_num . " " . $data[0] . (($cd_num > 1) ? $data[1] : "");
        }
        $mod = $factor;
    }
    return implode($separator, array_slice($cd_array, 0, $level));
}
?>

