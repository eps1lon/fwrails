<?php
require 'common.cron.php';
require 'curlm.class.php';

function curlm_exec($info) {
    if ($info['result'] !== CURLE_OK) {
        $info2 = curl_getinfo($info['handle']);
        echo "got " . Curlm::info_status($info['result']) . "for: ".
             $info2['url'];
    }
}

class Notify {
    private static $db_props = null;
    
    private $id;
    private $email;
    private $name;
    private $pattern;
    private $sender;
    private $text;
    
    public function __construct(Array $notify, $db = null) {
        if (is_null(self::$db_props)) {
            self::init_db_props($db);
        }
        
        foreach (self::$db_props as $prop) {
            if (!isset($notify[$prop]) && !is_null($notify[$prop])) {
                trigger_error("property `$prop` not given", E_USER_WARNING);
            } else {
                $this->{$prop} = $notify[$prop];
            }
        }
    }
    
    public function __get($prop) {
        $function_string = "__  get_$prop";
        
        if (is_callable(array($this, $function_string))) {
            return $this->{$function_string}();
        }
        
        if (in_array($prop, self::$db_props)) {
            return $this->{$prop};
        }
    }
    
    public function mail($world) {
        return mail($this->email, 
                    "Chat-Benachrichtigung in " . $world['name'], 
                    "Benachrichtigung wegen `" . $this->text . "`");
    }
    
    public function match($chat) {
        return preg_match("/$this->text/i", $chat);
    }
    
    private static function init_db_props($db = null) {
        self::$db_props = array('email');
        
        $fields = mysql_query("SHOW COLUMNS FROM notifies", $db);
        while ($field = mysql_fetch_assoc($fields)) {
            self::$db_props[] = $field['Field'];
        }
    }
}

function curlm_each($html, $handle, $j) {
    global $db, $worlds;
    
    $chats = array();
    
    // init class_names
    $sql_query = "SELECT DISTINCT class_name FROM notifies";
    $class_names = error_query($sql_query, $db);
    while ($class_name = mysql_fetch_array($class_names)) {
        $chats[$class_name['class_name']] = array();
    }
    
    preg_match_all('/<p class="(.*?)">(.*?)<\/p>/i', $html, $matches);
    
    for ($i = 0, $length = count($matches[1]); $i < $length; ++$i) {
        $chats[$matches[1][$i]][] = $matches[2][$i];
    }
    
    #print_r($chats);
    
    $matched_notifies = array();
    
    $sql_query = "SELECT readers.email, notifies.* FROM notifies_readers, notifies, readers ".
                 "WHERE readers.id = notifies_readers.reader_id AND notifies_readers.notify_id = notifies.id ".
                 "AND notifies_readers.world_id = '" . $worlds[$j]['id'] . "'";
    $result = error_query($sql_query, $db);
    while ($notify_raw = mysql_fetch_assoc($result)) {
        $notify = new Notify($notify_raw, $db);
        
        foreach ($chats[$notify->class_name] as $chat) {
            if ($notify->match($chat)) {
                $matched_notifies[$notify->id] = true;
            }
            
            if (isset($matched_notifies[$notify->id]) && $matched_notifies[$notify->id] === true) {
                $notify->mail($worlds[$j]);
            }
        }
    }
    
    print_r($matched_notifies);
}

$curlopts = array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_CONNECTTIMEOUT => 0,
    CURLOPT_TIMEOUT => 0
);

$curlm = new Curlm();
$curlm->setopts($curlopts);

$worlds = array();
$i = 0;

// get worlds
$sql_query = "SELECT worlds.*, languages.tld FROM worlds, languages WHERE ".
             "worlds.language_id = languages.id AND worlds.id IN (SELECT DISTINCT world_id FROM notifies_readers)";
$result = error_query($sql_query, $db);
while ($world = mysql_fetch_assoc($result)) {
    $worlds[$i++] = $world;
    
    $uri = "http://" . $world['subdomain'] . ".freewar." . $world['tld'] . "/freewar/internal/chattext.php";
    $curlm->add_handle($uri);
}

$curl_t = $curlm->exec("curlm_exec");
echo "crawled chats in " . ($curl_t * 1000) . "ms\n";

$curlm->each("curlm_each");
