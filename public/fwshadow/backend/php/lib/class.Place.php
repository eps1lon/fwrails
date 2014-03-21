<?php
require_once "class.Area.php";
require_once "class.Npc.php";

class Place {    
    const GFX_AS_REL = 0;
    const GFX_AS_ABS = 1;
    
    const BIT_OR      = 0;
    const BIT_AND     = 1;
    const BIT_XOR     = 2;
    const BIT_REPLACE = 3;
    
    const COLS = 'id name desc gfx pos_x pos_y flags area';
    const SET_FLAGS = 'holy closed';
    
    /**
     * place_id primary
     * @var int
     */
    private $id = null;
    /**
     * old id
     * @var int
     */
    private $id_old;
    /**
     * place_name
     * @var string
     */
    private $name = '';
    /**
     * place_type
     * @var string
     */
    private $desc = null;
    /**
     * gfx_file relative to mappath
     * @var string
     */
    private $gfx = 'std.jpg';
    /**
     * X Position
     * @var int
     */
    private $pos_x;
    /**
     * Y Position
     * @var int
     */
    private $pos_y;
    /**
     * place flags
     * @var int
     */
    private $flags = 0;
    /**
     * ID of the area foreign key
     * @var Area
     */
    private $area = null;
    /**
     * npcs on the place
     * @var array
     */
    private $npcs = null;
    
    /**
     * whether the object is empty or not
     * @var boolean
     */
    private $empty = false;
    
    
    /**
     *
     * @param mixed $mixed
     * @param ressource $db 
     */
    public function __construct($mixed, $db = null) {
        $data = array();
        
        if ( is_resource( $mixed ) && get_resource_type( $mixed ) === 'mysql result' ) {
            $data = mysql_fetch_assoc( $mixed );
        } else if ( is_array( $mixed ) ) {
            $data = $mixed;
        } else if ( is_numeric($mixed) ) {
            if ( $db === null ) {
                $db = mysql_connect();
            }
            
            $sql_query = "SELECT *, flags+0 AS flags FROM places WHERE id = '" . (int)$mixed . "'";
            $data = mysql_fetch_assoc( mysql_query($sql_query, $db) );
        } 
        
        if ( $data ) {
            foreach ($data as $key => $val) {
                $this->__set( $key, $val );
            }
        } else {
            $this->empty = true;
        }
    }
    
    /**
     * sets specified col
     * @param string $name
     * @param mixed $value 
     */
    public function __set ( $name, $value ) {
        if ( in_array( $name, self::cols(), true) ) {
            $func = "set_$name";
            $this->$func($value);
        } else {
            trigger_error( "$name is attribute of a place" );
        }
    }
    
    /**
     * returns specified col, null if the col was not found
     * @param string $name
     * @return mixed
     */
    public function __get ( $name ) {
        if ( in_array( $name, self::cols(), true) ) {
            $func = "get_$name";
            return $this->$func();
        }
        
        trigger_error( "$name is attribute of a place" );
        return null;
    }
    
    /**
     * returns the cols as an array
     * @staticvar boolean $cols
     * @return array
     */
    static public function cols () {
        static $cols = false;
        
        if ($cols === false) {
            $cols = explode(" ", self::COLS);
        }
        
        return $cols;
    }
    
    /**
     *
     * @staticvar boolean $flags
     * @return array
     */
    static public function flags_set () {
        static $flags = false;
        
        if ($flags === false) {
            $flags = explode(" ", self::SET_FLAGS);
        }
        
        return $flags;
    }
    
    /**
     * sets id
     * @param int $id 
     */
    public function set_id ( $id ) {
        if ( is_numeric( $id ) === true ) {
            $this->id_old = $this->id;
            $this->id = (int)$id;
        }
    }
    
    /**
     * sets name
     * @param string $name 
     */
    public function set_name ( $name ) {
        $this->name = (string)$name;
    }
    
    /**
     * sets desc
     * @param string $desc
     */
    public function set_desc ( $desc ) {
        $this->desc = (string)$desc;
    }
    
    /**
     * sets gfx
     * @param string $gfx 
     */
    public function set_gfx ( $gfx ) {
        $this->gfx = ($gfx);
    }
    
    /**
     * sets x pos
     * @param int $pos_x 
     */
    public function set_pos_x ( $pos_x ) {
        if ( is_numeric( $pos_x ) ) {
            $this->pos_x = (int)$pos_x;
        }
    }
    
    /**
     * sets y position
     * @param type $pos_y 
     */
    public function set_pos_y ( $pos_y ) {
        if ( is_numeric( $pos_y ) ) {
            $this->pos_y = (int)$pos_y;
        }
    }
    
    /**
     * sets flags
     * @param type $flag 
     */
    public function set_flags ( $flag, $operation = self::BIT_OR ) {
        if ( is_numeric($flags) ) {
            $flag = (int)$flag;
            
            switch ($operation) {
                case BIT_AND:
                    $this->flags &= $flag;
                    break;
                case BIT_XOR:
                    $this->flags ^= $flag;
                    break;
                case BIT_REPLACE:
                    $this->flags = $flag;
                    break;
                default:
                    $this->flags |= $flag;
                    break;
            }
        } else {
            if ( ($key = array_search ( $flag, self::flags_set()) ) !== false ) {
                $this->set_flags(pow( 2, $key ), $operation);
            }
        }
    }
    
    /**
     * sets area
     * @param Area $area 
     */
    public function set_area (Area $area ) {
        $this->area = $area;
    }
    
    /**
     * returns id
     * @return int
     */
    public function get_id () {
        return $this->id;
    }
    
    /**
     * returns name
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
    
    /**
     * returns desc
     * @return string
     */
    public function get_desc() {
        return $this->desc;
    }
    
    /**
     * returns gfx
     * @return string
     */
    public function get_gfx() {
        return $this->gfx;
    }
    
    /**
     * returns pos_x
     * @return int
     */
    public function get_pos_x() {
        return $this->pos_x;
    }
    
    /**
     * returns pos_y
     * @return int
     */
    public function get_pos_y() {
        return $this->pos_y;
    }
    
    /**
     *
     * @param string $format can be 'string' (names representing the flags) or 'int' (bitpattern)
     * @return mixed
     */
    public function get_flags($format = null) {
        if ($format === 'string') {
            $flags = array();
            
            foreach ( self::flags_set() as $i => $flag ) {
                if ( $this->flags & pow( 2, $i ) ) {
                    $flags[] = $flag;
                }
            }
            
            return implode( ', ', $flags );
        } 
        
        
        return $this->flags;
    }
    
    public function save ($db = null) {
        if ( $db === null ) {
            $db = mysql_connect();
        }
        
        if ( $this->id_old === null ) { // new place
            $sql_query = "INSERT INTO places (" . 
                         implode( ', ', array_filter( self::cols(), function ($col) {
                             return ($col !== 'id');
                         } ) ) . ") VALUES (";
            
            foreach ( self::cols() as $i => $col ) {
                if ( $i > 0 ) {
                    $sql_query .= ', ';
                }
                
                if ( is_null( $this->$col ) ) {
                    $sql_query .= "NULL";
                } else {
                    $sql_query .= "'" . $this->$col . "'";
                }
            }             
            
            $sql_query .= ')';
            
        } else {
            $sql_query = "UPDATE places SET ";
            
            foreach ( self::cols() as $i => $col ) {
                if ( $i > 0 ) {
                    $sql_query .= ', ';
                }
                
                $sql_query .= "$col = ";
                if ( is_null( $this->$col ) ) {
                    $sql_query .= "NULL";
                } else {
                    $sql_query .= "'" . $this->$col . "'";
                }
            } 
            
            $sql_query .= " WHERE id = '" . $this->id_old . "'";
        }
        
        echo $sql_query;
        //mysql_query($sql_query, $db);
        return mysql_affected_rows($db);
    }
    
    public function npcs ($add = null, $db = null) {
        if ( $db === null ) {
            $db = mysql_connect();
        }
        
        if ($this->npcs === null) {
            $sql_query = "SELECT * FROM npcs WHERE pos_x = '" . $this->pos_x . "' ".
                         "AND pos_y = '" . $this->pos_y . "'";
            $npcs = mysql_query( $sql_query, $db );
            
            while ( ($npc = new Npc( $result )) && $npc->exists() === false ) {
                $this->npcs[] = $npc;
            }
            
        }
        
        if ( is_array( $add ) ) {
            $npcs = array_merge();
        }
    }
    
    public function exists() {
        return $this->empty;
    }
};