<?php
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

class Shop {
    private $anzshopitems;
    private $buyrange;
    private $interval;
    private $items;
    private $sellrange;
    private $time;
    public $x;
    public $y;
    
    public $name;
    public $seed;
    
    static $sortKey;
    
    const LOOKFOR_MAX = PHP_INT_MAX;
    
    public function __construct($x, $y, $interval = 70000, Array $items = null, $anzshopitems = 0) {
        $this->x = (int)$x;
        $this->y = (int)$y;
        $this->interval = (int)$interval;
        $this->items = $items;
        
        $this->anzshopitems = $anzshopitems;
        $this->buyrange = array(85, 130);
        $this->sellrange = array(70, 115);
        $this->time = $_SERVER['REQUEST_TIME'];
        
        $this->seed = 1000;
    }
    
    public function srand() {
        srand((integer)($this->time / $this->interval) + $this->x + $this->y * $this->seed);
    }
    
    public function buyfactor() {
        $this->srand();
        return (float)rand($this->buyrange[0], $this->buyrange[1]) / (float)100;
    }
    
    public function sellfactor() {
        $this->buyfactor();
        return (float)rand($this->sellrange[0], $this->sellrange[1]) / (float)100;
    }
   
    public function sellable_items() {
        if ($this->anzshopitems === 0) {
            return $this->items;
        }
        
        $this->sellfactor();
        
        $sellable_items = $this->items;
        shuffle($sellable_items);
        $sellable_items = array_slice($sellable_items, 0, $this->anzshopitems);
        
        sort($sellable_items); 
        return $sellable_items;
    }
    
    public function forecast_intervall($add) {
        $this->time = $_SERVER['REQUEST_TIME'] - $_SERVER['REQUEST_TIME'] % $this->interval + (int)$add * $this->interval;
    }
    
    public function forecast_time($time) {
        $this->time = (int)$time;
    }
    
    public function changes_in() {
        return $this->interval - $this->time % $this->interval;
    }
    
    public function disable_buying() {
        $this->set_buyrange(array(0, 0));
    }
    
    public function disable_selling() {
        $this->set_sellrange(array(0, 0));
    }
    
    public function lookfor_buyfactor($factor) {
        return $this->lookfor_factor($factor, 'buy');
    }
    
    public function lookfor_sellfactor($factor) {
        return $this->lookfor_factor($factor, 'sell');
    }
    
    public function lookfor_item($item_name) {
        if (in_array($item_name, $this->items)) { // searched item is even sellable
            return $this->forecast(function ($buyfactor, $sellfactor, $items) use ($item_name) {
                if (in_array($item_name, $items)) {
                    return false; // item found, break the loop
                }
                return true;
            });
        }
        
        return false;
    }
    
    public function sells_items() {
        return $this->buyrange[0] !== 0;
    }
    
    public function buys_items() {
        return $this->sellrange[0] !== 0;
    }

    private function lookfor_factor($factor, $var) {
        if ($factor * 100 >= min($this->{$var . 'range'}) &&
            $factor * 100 <= max($this->{$var . 'range'})) {
            
            $shop = $this;
            return $this->forecast(function ($buyfactor, $sellfactor, $items) use ($factor, $var, $shop) {
                if ($factor === ${$var . 'factor'}) {
                    return false; // factor found
                }
                return true;
            });
        }

        return false;
    }
    
    private function forecast($callback) {
        $found = false;
        $temp = $this->time;
        
        #$epsilon = pow(10, -10);
        
        for ($i = floor(($temp - $_SERVER['REQUEST_TIME']) / $this->interval); $i <= self::LOOKFOR_MAX; ++$i) {
            $this->forecast_intervall($i);
            
            if ($callback($this->buyfactor(), $this->sellfactor(), $this->sellable_items()) === false) { // return === false means break
                $found = $this->time;
                break;
            }
        }
        
        $this->time = $temp;
        return $found;
    }
    
    public function __toString() {
        $ob = "";
        
        $tmp = $this->buyfactor();
        if ($tmp) {
            $ob .= "ek: $tmp\n";
        }
        
        $tmp = $this->sellfactor();
        if ($tmp) {
            $ob .= "vk: $tmp\n";
        }
        
        if ($this->anzshopitems > 0) {
            $ob .= "items: " . implode(", ", $this->sellable_items()) . "\n";
        }
        
        $ob .= "changes in " . cd_string($this->changes_in());
        
        return $ob;
    }
    
    public function __serializeable() {
        $obj = array(
            'changes_in' => $this->changes_in(),
            'x' => $this->x,
            'y' => $this->y,
            'name' => $this->name,
            'time' => $this->time,
            'time_rss' => date(DATE_RSS, $this->time)
        );
        
        $tmp = $this->buyfactor();
        if ($tmp) {
            $obj['buyfactor'] = $tmp;
        }
        
        $tmp = $this->sellfactor();
        if ($tmp) {
            $obj['sellfactor'] = $tmp;
        }
        
        if ($this->anzshopitems > 0) {
            $obj['current_items'] = $this->sellable_items();
            $obj['full_items'] = $this->get_items();
        }
        
        if ($this->sells_items()) {
            $obj['buyrange'] = array($this->buyrange[0] / 100, $this->buyrange[1] / 100);
        }
        
        if ($this->buys_items()) {
            $obj['sellrange'] = array($this->sellrange[0] / 100, $this->sellrange[1] / 100);
        }
        
        return $obj;
    }
    
    public function get_time() {
        return $this->time;
    }
    
    public function get_interval() {
        return $this->interval;
    }
    
    public function set_interval($interval) {
        $this->interval = (int)$interval;
    }
    
    public function get_items() {
        return $this->items;
    }
    
    public function set_items(Array $items) {
        $this->items = $items;
    }
    
    public function set_buyrange(Array $minmax) {
        $this->buyrange = $minmax;
        sort($this->buyrange);
    }
    
    public function get_buyrange() {
        return $this->buyrange;
    }
    
    public function set_sellrange(Array $minmax) {
        $this->sellrange = $minmax;
        sort($this->sellrange);
    }
    
    public function get_sellrange() {
        return $this->sellrange;
    }

    public static function sorter($a, $b)
    {
        return strcasecmp($a->{self::$sortKey}(), $b->{self::$sortKey}());
    }

    public static function sortByProp(&$collection, $prop)
    {
        self::$sortKey = $prop;
        uasort($collection, array(__CLASS__, 'sorter'));
    }
}