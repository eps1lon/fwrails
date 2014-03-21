<?php
$db = mysql_connect('localhost', 'slmania_reg', 'ztR8haql');
mysql_select_db('slmania', $db);

$sql_query = "SELECT *, UNIX_TIMESTAMP(created_at) as timestamp FROM bloodsamples ORDER BY created_at DESC";
$result = mysql_query($sql_query, $db) or die(mysql_error());
$old = mysql_fetch_assoc($result);

$diffs = array();
while ($sample = mysql_fetch_assoc($result)) {
    $diff = $old['timestamp'] - $sample['timestamp'];
    
    if ($diff < 1000 && $diff > 200) {
        $diffs[] = $diff;
    }
    
    $old = $sample;
}
echo "min: " . min($diffs) . "<br>";
echo "max: " . max($diffs) . "<br>";