<?php
header('Content-Type: text/html; charset=iso-8859-1;');
error_reporting(E_ALL);

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

echo mysql_client_encoding($db);

if (isset($_POST['npc_name'])) {
	$sql_query = "SELECT SUM(npcs.killcount) as killcount, name FROM npcs WHERE ".
				 "npcs.name = '" . utf8_decode(addslashes($_POST['npc_name'])) . "'";
	$result = mysql_query($sql_query, $db);
	
	if ($npc = mysql_fetch_assoc($result)) {
		$sql_query = "SELECT SUM(items_npcs.count) as count, items.name FROM npcs ".
					 "LEFT JOIN items_npcs ON npcs.id = items_npcs.npc_id ".
					 "LEFT JOIN items ON items.id = items_npcs.item_id ".
					 "WHERE npcs.name = '" . addslashes($npc['name']) .
					 "' GROUP BY items_npcs.item_id";
		$drops = mysql_query($sql_query, $db);
		
		echo '<p>' . htmlentities($npc['name']) . '</p><ul>';
		while ($drop = mysql_fetch_assoc($drops)) {
			echo '<li><em>' . htmlentities($drop['name']) . '</em>: '.
				 $drop['count'] . ' von ' . $npc['killcount'] . '</li>';
		}
		echo '</ul>';
	}
	
	
}
?>

<form action="?" method="POST">
	<select name="npc_name">
		<?php
			$sql_query = "SELECT name FROM npcs GROUP BY name ORDER BY name ASC";
			$result = mysql_query($sql_query, $db);
			while ($npc = mysql_fetch_assoc($result)) {
				echo '<option val="' . htmlentities($npc['name']). 
				(($npc['name'] == $_POST['npc_name']) ? ' selected="selected"' : '').'>'. 
				'">' . htmlentities($npc['name']) . '</option>';
			}
		?>
	</select>
	<input type="submit">
</form>
<form action="?" method="POST">
	<select name="item_name">
		<?php
			$sql_query = "SELECT name FROM items GROUP BY name ORDER BY name ASC";
			$result = mysql_query($sql_query, $db);
			while ($item = mysql_fetch_assoc($result)) {
				echo '<option val="' . htmlentities($item['name']) . '"'. 
				(($item['name'] == $_POST['item_name']) ? ' selected="selected"' : '').'>'. 
				htmlentities($item['name']) . '</option>';
			}
		?>
	</select>
	<input type="submit">
</form>