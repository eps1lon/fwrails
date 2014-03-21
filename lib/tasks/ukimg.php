<?php
require 'common.cron.php';
require 'curlm.class.php';

define('MAX_CURL_CONNECTIONS', max(1, $_GET['max_curl']));

#error_query("TRUNCATE categories", $db);
#error_query("TRUNCATE images", $db);

$files = array(
    // type => filename
    1 => "uk_imgs_region",
    2 => "uk_imgs_sets"
);
$type = 2;

$images_plain = file_get_contents("/var/www/js/grease/slmania/data/{$files[$type]}");

$sets = array();

$sql_query = "SELECT * FROM categories";
$categories = error_query($sql_query, $db);
while ($category = mysql_fetch_assoc($categories)) {
    $sets[$category['name']] = $category['id'];
}

if (isset($_GET['download'])) {
    $curlopts = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 50,
        CURLOPT_TIMEOUT => 0
    );
    
    $curlm = new Curlm();
    $curlm->setopts($curlopts);
    
    function curlm_exec($info) {
        if ($info['result'] !== CURLE_OK) {
            $info2 = curl_getinfo($info['handle']);
            echo "got " . Curlm::info_status($info['result']) . "for: ".
                 $info2['url'];
        }
    }

    function curlm_each($img, $handle, $j) {
        $root_src = $GLOBALS['root_src'];
        $root_dest = $GLOBALS['root_dest'];
        
        $info = curl_getinfo($handle);
        
        
        $filename = $root_dest . str_replace($root_src, "", $info['url']);
        $path = pathinfo($filename)['dirname'];
        
        #echo $filename;
        
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        file_put_contents($filename, $img);
    }
    
    $root_src = "http://welt3.freewar.de/freewar/images/map/";
    $root_dest = "/var/www/freewar3/public/ukimgs/";

    $sql_query = "SELECT images.filename FROM images, categories ".
                 "WHERE images.category_id = categories.id ";//."LIMIT " . MAX_CURL_CONNECTIONS;
    $images = error_query($sql_query, $db);
    
    $curl_i = 0;
    $curl_i_length = mysql_num_rows($images);
    
    while ($image = mysql_fetch_assoc($images)) {
        $url = $root_src . $image['filename'];
        
        if (!file_exists($root_dest . $image['filename'])) {
            // add handle
            $curlm->add_handle($url);
            $curl_i++;
        }
        
        if (($curl_i + 1) % MAX_CURL_CONNECTIONS === 0 || $curl_i === $curl_i_length - 1) { // max curl connections
            // exec
            $curl_t = $curlm->exec("curlm_exec");
            $curlm->each("curlm_each");
            
            unset($curlm);
            $curlm = new Curlm();
            $curlm->setopts($curlopts);
        }
    }
}
else if (isset($_GET['insert'])) {
    foreach (explode("\n", $images_plain) as $line) {
        #echo $line;
        if (!preg_match("/\((.+)\) (.+)/i", $line, $data)) {
            echo "'$line'\n";
        } else if ($data[2] == "") {
            echo "'$line'\n";
        }

        if (isset($sets[$data[1]])) {
            $set_id = $sets[$data[1]];
        } else {
            $sql_query = "INSERT INTO categories (name, category_type, updated_at, created_at) VALUES ".
                         "('" . mysql_real_escape_string($data[1], $db) . "', '$type', '$now', '$now')";
            error_query($sql_query, $db);

            $set_id = mysql_insert_id($db);
            $sets[$data[1]] = $set_id;
        }

        $sql_query = "INSERT INTO images (category_id, filename, created_at, updated_at) VALUES ".
                     "($set_id, '" . mysql_real_escape_string($data[2], $db) . "', '$now', '$now')";
        error_query($sql_query, $db);
        #break;
    }
} else {
    echo "?insert > Einfügen\n";
    echo "?download > Download\n";
}

