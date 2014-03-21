<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset="utf-8">
    </head>
    <body>
<?php
echo '<br><form method="POST" action="" id="ticket_alter" name="ticket_alter">';
echo '<label for="paintings">Zeichnungen</label>';
echo '<textarea rows="10" cols="50" id="paintings" name="paintings">' . htmlentities($_POST['paintings']);
echo '</textarea><input type="submit">';
echo '</form>';

if (isset($_POST['paintings'])) {
	$plain_text = file_get_contents("../data/paintings");
	$current_paintings = array_map('trim', explode("\n", str_replace("", "", $plain_text)));
	
	if (isset($_GET['no_prefix']) === false) {
		$pattern = "/Zeichnung von [äöüßa-z0-9_\-]+/i";
		preg_match_all($pattern, $_POST['paintings'], $matches);
		$paintings = $matches[0];
	} else {
		$paintings = explode("\n", $_POST['paintings']);
		
		foreach ($paintings as $key => $painting) {
			$paintings[$key] = "Zeichnung von " . $painting;
		}
	}
	
	$haves = array_intersect($paintings, $current_paintings);
	$new = array_diff($paintings, $current_paintings);
	
	echo '<p>' . count($paintings) . ' gegeben, ' . count($haves) . ' zurück, '.
	     count($new) . ' * 500 = ' . (count($new) * 500) . '</p>';
	echo '<h1>neu</h1>';
	echo '<ol>';
	foreach ($new as $painting) {
		echo '<li>' . $painting . '</li>';
	}
	echo '</ol>';
	echo '<h1>zurück</h1>';
	echo '<ol>';
	foreach ($haves as $painting) {
		echo '<li>' . $painting . '</li>';
	}
	echo '</ol>';
}


?>