<?php
error_reporting(0);
header('Content-Type: application/json; charset=iso-8859-1');
header('Access-Control-Allow-Origin: *');

$ob = array("msg" => "", "what" => array());

function shutdown () {
    echo json_encode($GLOBALS['ob']);
}

function mysql_encode($var, $db) {
    if (is_numeric($var)) {
        return "'" . (int)($var) . "'";
    }
    if (is_null($var)) {
        return 'NULL';
    }
    return "'" . mysql_real_escape_string(utf8_encode($var), $db) . "'";
}

function db_error_handler ($db) {
    $ob['err'] = "mysql-error#" . mysql_errno($db) . ": \n" . 
                 mysql_error($db);
    
    print_r(debug_backtrace());
    
    exit;
}

function error_query($sql_query) {
    global $db;
    global $connection_count;
    
    $result = mysql_query($sql_query, $db);
    if ($result === false) {
        if (mysql_errno($db) == 2006) { // server gone away
            
            /* re-connect
            mysql_close($db);
            $db = db_connect();
            
            return error_query($sql_query, $db);//*/
        }

        db_error_handler($db);   
    }
    return $result;
}

register_shutdown_function("shutdown");

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
        
if (!$db) {
    $ob['msg'] = "Verbindung fehlgeschlagen";
    exit;
}

mysql_select_db("slmania", $db);
if (!$db) {
    $ob['msg'] = "Verbindung fehlgeschlagen";
    exit;
}

$sql_query = "SELECT id FROM users WHERE authenticity_token = '" . mysql_real_escape_string($_POST['id'], $db) . "'";
$result = error_query($sql_query, $db);
if ($user = mysql_fetch_row($result)) { // User stimmt
    if (isset($_POST['text'])) {
        error_query("INSERT INTO bloodsamples (text, created_at) VALUES ('" . addslashes($_POST['text']) . "', NOW())", $db);
        $ob['msg'] = mysql_affected_rows($db) . " eingetragen";
    } else {
        $ob['msg'] = "kein Text erhalten";
    }
} else {
    $ob['msg'] = "User konnte nicht authentiziert werden";
}