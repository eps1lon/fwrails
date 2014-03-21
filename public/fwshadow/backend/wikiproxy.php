<?php
header('Access-Control-Allow-Origin: *');

switch ($_GET['type']) {
    case "category":
        die(file_get_contents("http://www.fwwiki.de/index.php/Kategorie:Felder"));
        break;
    case "field":
        die(file_get_contents("http://www.fwwiki.de/index.php/Felder:" . urlencode($_GET['site']) . "?action=edit"));
        break;
}