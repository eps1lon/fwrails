<?php
header('Content-Type: text/plain; charset=utf-8');

// cron workaround
chdir(dirname(__FILE__));

require_once '../php/rails_const.php';

error_reporting(E_ALL ^ E_DEPRECATED);

ini_set('max_execution_time', 600);
list($usec, $sec) = explode(" ", microtime());
$starttime = (float)$usec + (float)$sec;

ini_set('error_prepend_string', '');
ini_set('error_append_string', '');

// environment
define('ENV', RAILS_ENV);

// root
define('ROOT', RAILS_ROOT . '/');

// cap shared path
if (getenv('SHARED_PATH')) {
    define('SHARED_PATH', getenv('SHARED_PATH'));
} else {
    define('SHARED_PATH', ROOT);
}

# deprecated
if (!defined('ENT_HTML401')) {
    define('ENT_HTML401', 0);
}

define('CSV_DELIMITER', ';');

$curle_c = array();
foreach (explode(" ", "OK UNSUPPORTED_PROTOCOL FAILED_INIT URL_MALFORMAT ".
                      "URL_MALFORMAT_USER COULDNT_RESOLVE_PROXY COULDNT_RESOLVE_HOST ".
                      "COULDNT_CONNECT PARTIAL_FILE HTTP_NOT_FOUND WRITE_ERROR ".
                      "MALFORMAT_USER READ_ERROR OUT_OF_MEMORY OPERATION_TIMEOUTED ".
                      "HTTP_RANGE_ERROR HTTP_POST_ERROR FILE_COULDNT_READ_FILE ".
                      "LIBRARY_NOT_FOUND FUNCTION_NOT_FOUND ABORTED_BY_CALLBACK ".
                      "BAD_FUNCTION_ARGUMENT BAD_CALLING_ORDER HTTP_PORT_FAILED ".
                      "TOO_MANY_REDIRECTS OBSOLETE GOT_NOTHING SEND_ERROR RECV_ERROR ".
                      "SHARE_IN_USE BAD_CONTENT_ENCODING FILESIZE_EXCEEDED") as $c_name) {
    
    $curle_c[constant("CURLE_$c_name")] = $c_name;
}

function mysql_encode($var, $db) {
    if (is_numeric($var)) {
        return "'" . (int)($var) . "'";
    }
    if (is_null($var)) {
        return 'NULL';
    }
    return "'" . mysql_real_escape_string($var, $db) . "'";
}

function error_query($sql_query, $db = null, $ignore = false) {
    global $connection_count;
    
    $result = mysql_query($sql_query, $db);
    if ($result === false) {
        if (mysql_errno($db) == 2006) { // server gone away
            
            // re-connect
            mysql_close($db);
            $db = db_connect();
            
            return error_query($sql_query, $db);
        }

        db_error_handler($db, $ignore);   
    }
    return $result;
}

function db_error_handler ($db, $ignore = false) {
    echo "mysql-error#" . mysql_errno($db) . ": \n" . 
          mysql_error($db);
    
    print_r(array_slice(debug_backtrace(), 1));
    
    if ($ignore === false) {
        exit;
    }
}

$connection_count = 0;
function db_connect($name = null) {
    global $connection_count;
    $db = mysql_connect('localhost', 'stellari_cron', 'norcser', true);
    $connection_count++;
    
    if ($db === false) {
        db_error_handler($db);
    }
    
    if ($name === null) {
        $name = 'stellari_freewar3_' . ENV;
    }
    if (!empty($name)) {
        if (mysql_select_db($name, $db) === false) {
            db_error_handler($db);
        }
    }
    
    mysql_set_charset("iso-8859-1", $db);
    
    return $db;
}

function implode_runtime(&$sql_query, $i, $insert_query, $db = null, $glue = ", ") {
    if (($i + 1) % MAX_QUERIES === 1) { // reached max_queries
        if ($i >= MAX_QUERIES - 1) {
            error_query($sql_query, $db);
        }
        $sql_query = $insert_query;
    } else {
        $sql_query .= $glue;
    }
}

function shutdown() {
    global $connection_count;
    global $starttime;
    list($usec, $sec) = explode(" ", microtime());
    echo "\nCron took " . ((((float)$usec + (float)$sec) - $starttime) * 1000) . " ms (" . ($sec - $GLOBALS['sec']) . "s)".
         "\nand connected $connection_count times\n";
}
register_shutdown_function("shutdown");

function create_dumps($db) {
    $file = basename($_SERVER['PHP_SELF'], ".php");
    include "{$file}_create.php";
}

function decode_str($s) {
    return html_entity_decode(stripslashes($s), ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
}

echo "running in " . ENV . " environment\n";

date_default_timezone_set('Europe/Berlin');

$now = strftime("%Y-%m-%d %H:%M:%S", $_SERVER['REQUEST_TIME']);
$db = db_connect();
$backup = db_connect('');
