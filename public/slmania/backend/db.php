<?php
require_once 'db.consts.php';
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        
if (!$db) {
    $ob['msg'] = "Verbindung fehlgeschlagen";
    exit;
}

mysql_select_db(DB_NAME, $db);
if (!$db) {
    $ob['msg'] = "Verbindung fehlgeschlagen";
    exit;
}