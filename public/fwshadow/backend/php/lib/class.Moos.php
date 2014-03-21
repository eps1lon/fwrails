<?php
require_once 'class.Rand.php';

class Moos {
    const MIN_STAGE = 1;
    const MAX_STAGE = 200;
    const MIN_VALUE = 1;
    
    const LEVEL_UP_STAGE = 1;
    const LEVEL_UP_A = 2;
    const LEVEL_UP_W = 4;
    const LEVEL_UP_P = 8;
    const LEVEL_UP_M = 16;
    const LEVEL_UP_STD = 31;
    
    private static $value_names = array(
        'a' => 'Anreicherung',
        'w' => 'Wachstum',
        'p' => 'Potential',
        'm' => 'Mutation'
    );
    
    private $stage = self::MIN_STAGE;
    private $base_values = array();
    private $values = array();
    
    public function  __construct ($stage = 0, Array $base_values = array(), Array $values = array()) {
        // init
        foreach (array_keys(self::$value_names) as $var) {
            $this->__set_value($var); // value
            $this->__set_value($var, null, true); // base value
            
        }
        
        if ($stage < self::MIN_STAGE) {
            $this->init();
        } else {
            $this->__set_stage($stage);
        
            // init the base values which are not given
            $no_base_values = array_diff_key(self::$value_names, $base_values);
            if (count($no_base_values) > 0) {
                foreach (array_keys($no_base_values) as $var) {
                    $this->new_base_value($var);
                }
            }

            // init the given base values
            foreach ($base_values as $var => $val) {
                $this->__set_value($var, $val, true);
            }


            // init the values which are not given
            $no_values = array_diff_key(self::$value_names, $values);
            if (count($no_values) > 0) {
                foreach (array_keys($no_values) as $var) {
                    $this->re_simulate(constant("self::LEVEL_UP_" . strtoupper($var)));
                }
            }

            // init the given values
            foreach ($values as $var => $val) {
                $this->__set_value($var, $val);
            }
        } 
    }
    
    public function __get($name) {
        $function_string = "__get_$name";
        if (is_callable(array($this, $function_string))) {
            return $this->{$function_string}();
        }
    }


    public function __toString() {
        $string = "Stufe: " . $this->stage . "<br>".
                  "<table><tr><td>Name</td><td>Grundwert</td><td>Wert</td></tr>";
        
        $sum_base = 0;
        $sum_values = 0;
        foreach (self::$value_names as $var => $name) {
            $sum_base += $this->base_values[$var];
            $sum_values += $this->values[$var];
            
            $string .= "<tr><td>$name</td><td>" . $this->base_values[$var] . "</td>".
                       "<td>" . $this->values[$var] . "</td></tr>";
        }
        
        $string .= "<tr><td>Summe</td><td>$sum_base</td><td>$sum_values</td></tr></table>";
        
        return $string;
    }
    
    public function init() {
        $this->stage = self::MIN_STAGE;
        $this->new_base_values();
    }
    
    public function cross_with(Moos $moos2) {
        $changes = self::initial_values(0);
        
        $intersection = $this->intersect_with($moos2);
        $cross_stage = $this->cross_with_stage($moos2);
        
        foreach ($intersection as $var => $val) {
            $changes[$var] = Rand::trend(-1, 1, 2);
            $intersection[$var] = $val + $changes[$var];
            
            $this->__set_value($var, $intersection[$var], true);
        }
        
        $this->__set_stage($cross_stage);
        
        $this->re_simulate();
        
        return $changes;
    }
    
    public function cross_with_stage(Moos $moos2) {
        return self::cross_stage($this->stage, $moos2->stage);
    }
    
    public function intersect_with(Moos $moos2) {
        $intersection = array();
        
        foreach (array_keys(self::$value_names) as $var) {
            $intersection[$var] = $this->intersect_value($var, $moos2);
        }
        
        return $intersection;
    }
    
    public function new_base_values() {
        foreach (array_keys(self::$value_names) as $var) {
            $this->new_base_value($var);
        }
    }
    
    public function new_base_value($var) {
        $this->base_values[$var] = rand(1, 10);
        $this->values[$var] = 0;
        
        $this->re_simulate(constant("self::LEVEL_UP_" . strtoupper($var)));
    }
    
    public function re_simulate($only = self::LEVEL_UP_STD) {
        $old_stage = $this->stage;

        $only = $only | self::LEVEL_UP_STAGE;
        
        for ($this->stage = self::MIN_STAGE - 1; $this->stage < $old_stage;) {
            $this->level_up($only);
        }
    }
    
    public function level_up($only = self::LEVEL_UP_STD) {
        $changes = self::initial_values(0);
        
        if ($this->stage < self::MAX_STAGE) {
            if ($only & self::LEVEL_UP_STAGE) {
                $this->stage++;
            }
            
            foreach (array_keys(self::$value_names) as $var) {
                if ($only & constant("self::LEVEL_UP_" . strtoupper($var))) {
                    $changes[$var] = Rand::rand(1, $this->base_values[$var]);
                }
                
                $this->values[$var] += $changes[$var];
            }
        }
        
        return $changes;
    }
    
    public function scion(&$changes = null) {
        $values = $this->base_values;
        $changes = self::initial_values(0);
        
        foreach (array_keys(self::$value_names) as $var) {
            $changes[$var] = Rand::rand(-1, 1);
            $values[$var] += $changes[$var];
        }
        
        return new Moos(1, $values);
    }
    
    public static function cross_stage($stage1, $stage2) {
        $maxstage = max($stage1, $stage2);
        return $maxstage + floor(($maxstage / 5) / max(abs($stage1 - $stage2), 1));
    }
    
    public static function cross_value_rand() {
        return Rand::norm(1, 1, -1, 2);
    }
    
    private static function initial_values($inital = 0) {
        return array_combine(array_keys(self::$value_names), 
                             array_fill(0, count(self::$value_names), $inital));
    }

    private function intersect_value($var, Moos $moos2) {
        if (Rand::rand(0, 1)) {
            $round = "floor";
        } else {
            $round = "ceil";
        }
        
        return $round(($this->base_values[$var] + $moos2->base_values[$var]) / 2);
    }
    
    private function __get_stage () {
        return $this->stage;
    }
    
    private function __get_base_values() {
        return $this->base_values;
    }
    
    private function __get_values() {
        return $this->values;
    }
    
    private function __set_stage ($stage) {
        $this->stage = max(self::MIN_STAGE, (int)$stage);
    }
    
    private function __set_value($name, $val = self::MIN_VALUE, $base = false) {
        if ($base === true) {
            $prefix = "base_";
        } else {
            $prefix = "";
        }
        
        if (isset(self::$value_names[$name])) {
            $this->{$prefix."values"}[$name] = max(self::MIN_VALUE, (int)$val);
        }
    }
}