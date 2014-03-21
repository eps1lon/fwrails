<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

preg_match_all("/Zeichnung von [äöüßa-z\-]+/i", $_POST['plain'], $paintings);
echo implode("<br>", $paintings[0]);
?>
<form method="POST">
    <textarea name="plain"><?= $_POST['plain'] ?></textarea>
    <input type="submit">
</form>
    </body></html>