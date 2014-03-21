<?php
require_once 'db.php';
require_once 'version.php';

$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);

if (!$db) {
    echo "Verbindung zum Host nicht möglich! Verbindungsdaten überprüfen.\n";
} else {
    if (!mysql_select_db(DB_NAME, $db)) {
        echo "Datenbank existiert nicht. Bitte install.php vorher ausführen.";
    }
}

if ($db) {
    echo "Datenbankverbindung hergestellt\n";
}