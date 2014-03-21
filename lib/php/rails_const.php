<?php

define('RAILS_ROOT', realpath('../../'));

$htaccess = file_get_contents(RAILS_ROOT . '/public/.htaccess');

if (!defined('RAILS_ENV')) {
    if (preg_match("/RailsEnv\s+(\w+)/i", $htaccess, $matches)) {
        define('RAILS_ENV', $matches[1]);
    } else {
        define('RAILS_ENV', 'test');
    }
}
