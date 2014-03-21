<?php
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

$sql_query = "SELECT name FROM items ORDER BY name";
$items = mysql_query($sql_query, $db);

if (isset($_POST['item_name'])) {
    $item_name = mysql_real_escape_string($_POST['item_name']);
    $sql_query = "SELECT places.* FROM items, items_places, places ".
                 "WHERE items.name = '$item_name' AND items_places.item_id = items.id ".
                 "AND items_places.place_id = places.id";
    $places = mysql_query($sql_query, $db);
    
    echo "<p>" . htmlspecialchars($_POST['item_name']) . ":</p><ul>";
    while ($place = mysql_fetch_assoc($places)) {
        echo "<li>" . $place['name'] . " Position X: " . $place['pos_x'] . " Y: " . $place['pos_y'] . "</li>";
    }
    echo "</ul>";
}
?>
<form method="post">
    <label for="item_name">Name:</label>
    <select id="item_name" name="item_name">
        <?php 
        while ($item = mysql_fetch_assoc($items)) {
            ?>
            <option value="<?= $item['name'] ?>"<?=$item['name'] == $_POST['item_name'] ? 'selected="selected"' : ''?>>
                <?= $item['name'] ?>
            </option>
            <?php
        }
        ?>        
    </select>
    <input type="submit">
</form>