<pre>
<?php
require_once 'db.php';
require_once 'version.php';

$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);

if (!$db) {
    echo "Verbindung zum Host nicht möglich!\n";
} else {
    if (!mysql_query("CREATE DATABASE IF NOT EXISTST " . DB_NAME, $db)) {
        if (!mysql_select_db(DB_NAME, $db)) {
            echo "Datenbank konnte nicht erstellt werden.\n";
        }
    } else {
        mysql_select_db(DB_NAME, $db);
    }
}

if ($db) {
    echo "Datenbankverbindung hergestellt\n";

    $queries = explode(" ;\n", file_get_contents('database.sql'));
    
    // Tabellenstruktur
    // Items
    $sql_query = $queries[0];
    if (!mysql_query($sql_query, $db)) {
        if (mysql_errno($db) !== 1050) {
            echo "Item-Tabelle konnte nicht erstellt werden.\n";
            echo mysql_error() . "\n";
        }
    } else {
        echo "Item-Tabelle erstellt!\n";
    }
    
    // npcs
    $sql_query = $queries[3];
    if (!mysql_query($sql_query, $db)) {
        if (mysql_errno($db) !== 1050) {
            echo "NPC-Tabelle konnte nicht erstellt werden.\n";
            echo mysql_error() . "\n";
        }
    }else {
        echo "NPC-Tabelle erstellt!\n";
    }
    
    // users
    $sql_query = $queries[4];
    if (!mysql_query($sql_query, $db)) {
        if (mysql_errno($db) !== 1050) {
            echo "User-Tabelle konnte nicht erstellt werden.\n";
            echo mysql_error() . "\n";
        }
    }
    
    // items-npcs
    $sql_query = $queries[1];
    if (!mysql_query($sql_query, $db)) {
        if (mysql_errno($db) !== 1050) {
            echo "Items-NPCs-Tabelle konnte nicht erstellt werden.\n";
            echo mysql_error() . "\n";
        }
    }else {
        echo "Items-NPCs-Tabelle erstellt!\n";
    }
    
    // items-places
    $sql_query = $queries[2];
    if (!mysql_query($sql_query, $db)) {
        if (mysql_errno($db) !== 1050) {
            echo "Items-Felder-Tabelle konnte nicht erstellt werden.\n";
            echo mysql_error() . "\n";
        }
    }else {
        echo "Items-Felder-Tabelle erstellt!\n";
    }
    
    // user einfügen
    echo "</pre>";
    
    if (isset($_POST['username']) && isset($_POST['token'])) {
        $sql_query = "INSERT INTO users (name, authenticity_token) VALUES ".
                     "('" . mysql_real_escape_string($_POST['username']) . "', ".
                     "'" . mysql_real_escape_string($_POST['token']) . "')";
        mysql_query($sql_query, $db);
        echo "<p>`" . $_POST['username'] . "` mit Schlüssel `" . $_POST['token'] . "` eingefügt</p>";
    } 
    
    ?>
        <h1>Nutzer hinzufügen</h1>
        <p>Der Name der Benutzer dient lediglich zur Identifikation. 
           Der Schlüssel muss später in `javascripts/main.js`    in Zeile 2 als
           unter `AUTHENTICITY_TOKEN = 'Beispielschlüssel'` eingetragen werden.</p>
        <form action="" method="POST">
            Name: <input type="text" name="username">
            Schlüssel: <input type="text" name="token">
            <input type="submit">
        </form>
    <?php
} else {
    echo "</pre>";
}

require_once 'migrate.php';