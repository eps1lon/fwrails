<table>
    <tr>
        <th>Name</th>
        <th>Staerke</th>
        <th>Leben</th>
        <th>X</th>
        <th>Y</th>
        <th>Anzahl</th>
        <th>Wiki</th>
    </tr>
<?php
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

$sql_query = "SELECT name FROM npcs WHERE id > 0 GROUP BY name";
$names = mysql_query($sql_query, $db);
while ($name = mysql_fetch_row($names)) {
    $sql_query = "SELECT strength, live, pos_x, pos_y, COUNT(*) as count FROM npcs ".
                 "WHERE name = '" . mysql_real_escape_string($name[0], $db) . "' AND live > 0 AND strength > 0 ".
                 "GROUP BY strength, live";
    $exceptions = mysql_query($sql_query, $db);
    if (mysql_num_rows($exceptions) > 1) {
        $exception = mysql_fetch_assoc($exceptions);
        
        echo "<tr><td rowspan='" . mysql_num_rows($exceptions) . "' style='border: 1px solid red;'>" . $name[0] . "</td>";
        echo "<td>" . $exception['strength'] . "</td><td>" . $exception['live'] . "</td>".
             "<td>" . $exception['pos_x'] . "</td><td>" . $exception['pos_y'] . "</td>".
             "<td>" . $exception['count'] . "</td><td>-->{{NPC/Ausnahme|A=" . $exception['strength'] . "|LP=" . $exception['live'] . "}}<&#33;--</td></tr>";
       
        while ($exception = mysql_fetch_assoc($exceptions)) {
            echo "<td>" . $exception['strength'] . "</td><td>" . $exception['live'] . "</td>".
                 "<td>" . $exception['pos_x'] . "</td><td>" . $exception['pos_y'] . "</td>".
                 "<td>" . $exception['count'] . "</td><td>-->{{NPC/Ausnahme|A=" . $exception['strength'] . "|LP=" . $exception['live'] . "}}<&#33;--</td></tr>";
        }
    }
}
?>
</table>