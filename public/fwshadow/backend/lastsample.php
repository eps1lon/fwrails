<?php
error_reporting(0);
header('Access-Control-Allow-Origin: *');

$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db("slmania", $db);

$last = mysql_fetch_row(mysql_query("SELECT UNIX_TIMESTAMP(created_at) FROM  bloodsamples ORDER BY created_at DESC LIMIT 1", $db));
echo $last[0];