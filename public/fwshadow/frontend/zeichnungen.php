<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
<?php
define('EXTRACT_W_PRE', 1);
define('PAINTING_DELIMITER', ';');

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

function get_token($ticket) {
	if (is_null($ticket['token'])) {
		return sha1($ticket['owner'] . md5($ticket['paintings']) . $ticket['created_at']);
	} else {
		return $ticket['token'];
	}
}

function create_link($ticket) {
	return 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?ticket=' . urlencode(get_token($ticket));
}

function extract_paintings($ticket, $options = 0) {
	if (is_null($ticket['paintings'])) {
		return 'empty';
	} else {
		if ($options & EXTRACT_W_PRE) {
			$replace = 'Zeichnung von ';
			$ticket['paintings'] = $replace . str_replace(PAINTING_DELIMITER, PAINTING_DELIMITER . $replace, $ticket['paintings']);
		}
		
		return implode("\r\n", explode(PAINTING_DELIMITER, $ticket['paintings']));
	}
}

if (isset($_GET['ticket'])) {
	echo '<h1>Ticketverwaltung</h1>';
	$sql_query = "SELECT * FROM tickets WHERE token = '" . mysql_real_escape_string($_GET['ticket'], $db) . "'";
	$result = mysql_query($sql_query, $db);
	if ($ticket = mysql_fetch_assoc($result)) {
		echo '<p><strong>Eigentümer: </strong><em>' . htmlentities($ticket['owner']) . '</em></p>';
		echo '<p><strong>Link: </strong><a href="' . htmlentities(create_link($ticket)) . '">' . htmlentities(create_link($ticket)) . '</a></p>';
	} else {
		echo '<p>Ticket abgelaufen!</p>';
	}
	
	echo '<h1>Ticket verändern</h1>';
} else {
	echo '<h1>Ticket erstellen</h1>';
}

echo '<form method="POST" action="?" id="ticket_alter" name="ticket_alter">';
echo '<label for="ticket_owner">Eigentümer: </label>';
echo '<input type="text" id="ticket_owner" name="ticket_owner" value="' . htmlentities($ticket['owner']) . '">';
echo '<label for="ticket_paintings">enthaltene Zeichnungen</label>';
echo '<textarea rows="5" cols="100" id="ticket_paintings" name="ticket_paintings">' . htmlentities(extract_paintings($ticket, EXTRACT_W_PRE)) . "</textarea>";
echo '</form>';


?>
</body></html>