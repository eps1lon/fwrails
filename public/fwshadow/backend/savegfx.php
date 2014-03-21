<?php
ini_set('max_execution_time', 0); 
header('Content-Type: text/plain; charset=utf-8');
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
      mysql_select_db("slmania", $db);

if (isset($_GET['gfx'])) {
    $src = imagecreatefromjpeg($_GET['gfx']);
    imagejpeg($src, "map/" . basename($_GET['gfx']));
} else {
    $sql_query = "SELECT gfx FROM places WHERE gfx IS NOT NULL GROUP BY gfx";
    $result = mysql_query($sql_query, $db);
    
    $master = curl_multi_init();
    $images = array();
    $places = array();
    $i = 0;
    $i_length = mysql_num_rows($result);
    
    
    while ($place = mysql_fetch_assoc($result)) {       
        ++$i;
        
        $url = "http://welt1.freewar.de/freewar/images/map/" . $place['gfx'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        
        $images[] = $ch;
        $places[] = $place;
        curl_multi_add_handle($master, end($images));
        
        if ($i % 30 === 0 || $i === $i_length - 1) { // max curl connections
            // exec curl
            do {
                curl_multi_exec($master, $running);
            } while ($running > 0);
            
            for($j = 0, $length = count($images); $j < $length; $j++)
            {
                file_put_contents("map/" . $places[$j]['gfx'], curl_multi_getcontent($images[$j]));

                
                curl_multi_remove_handle($master, $images[$j]);
            }
            
            curl_multi_close($master);
            $master = curl_multi_init();
            $images = array();
            $places = array();
        }
    }
}

echo "$i images\n";