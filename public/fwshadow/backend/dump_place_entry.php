<?php
header('Content-Type: text/html; charset=utf-8');

if (isset($_POST['npcs'])) {
    $db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
    mysql_select_db("slmania", $db);
    
    $areas = json_decode($_POST['npcs']);
    
    foreach ($areas as $places) {
        foreach ($places as $place) {
            mysql_query("INSERT IGNORE INTO places (pos_x, pos_y, name) VALUES ('" . $place->pos_x . "', '" . $place->pos_y . "', '" . utf8_decode($place->name) . "')", $db) or die(mysql_error());
        }
    }
}
?>
<form method="POST">
    <textarea name="npcs"><?php echo $_POST['npcs'] ?></textarea>
    <input type="submit" />
</form>