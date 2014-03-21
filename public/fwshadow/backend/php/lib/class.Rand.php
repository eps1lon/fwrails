<?php
class Rand {
    public function __construct() {}
    
    public static function rand($min = null, $max = null) {
        if (is_null($min)) {
            return mt_rand();
        } else {
            if (is_null($max)) {
                $max = mt_getrandmax();
            }
            return mt_rand($min, $max);
        }
    }
    
    public static function seed($seed = null) {
        return mt_srand($seed);
    }
    
    public static function rand01() {
        return self::rand() / mt_getrandmax();
    }
    
    public static function rand11() {
        return self::rand01() * 2 - 1;
    }
    
    public static function norm_std() {
        do {
            $u1 = self::rand11();
            $u2 = self::rand11();

            $q = pow($u1, 2) + pow($u2, 2);
        } while ($q == 0 || $q > 1);

        $p = sqrt((-2 * log($q)) / $q);

        $x1 = $u1 * $p;
        $x2 = $u2 * $p;

        if (self::rand(0, 1)) {
            $x = $x1;
        } else {
            $x = $x2;
        }
        
        return $x;
    }
    
    // polar
    public static function norm2_std() {
        return cos(2 * pi() * self::rand01()) * sqrt(-2 * log(self::rand01()));
    }
    
    public static function norm($a, $b, $min, $max) {
        do {
            $x = round(self::norm_std() * $b) + $a;
        } while ($x > $max || $x < $min);
        
        
        return $x;
    }
    
    public static function trend($min, $max, $n = 2) {
        $numbers = array();
        for ($n; $n > 0; --$n) {
            $numbers[] = self::rand($min, $max);
        }

        return max($numbers);
    }
}