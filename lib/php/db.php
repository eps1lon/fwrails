<?php
require_once 'rails_const.php';

$yml_file = RAILS_ROOT . '/config/database.yml';

$connections = yaml_parse_file($yml_file);     

if (isset($connections[RAILS_ENV])) {
    $connection = $connections[RAILS_ENV];
    
    if ($connection['adapter'] == 'mysql2') {
        $host = 'localhost';
        // mysql adapter
        $db = mysql_connect($host, 
                            $connection['username'],
                            $connection['password']);
        mysql_select_db($connection['database'], $db);
        mysql_set_charset($connection['encoding'], $db);
        
        // mysqli adapter
        $dbi = new Mysqli($host,
                          $connection['username'],
                          $connection['password'],
                          $connection['database']);
        $dbi->set_charset($connection['encoding']);
    }
    
} else {
    trigger_error(E_USER_WARNING, 'no connection configuration found in ' .
                                  RAILS_ENV . ' environment');
}
