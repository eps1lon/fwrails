<?php
require 'common.cron.php';
//*
function parse_interval($string) {
    $seconds = -1;
    
    preg_match("/(\d+) ([a-z]+)/i", $string, $matches);
    
    $units = array(
        "minute" => 60,
        "hour" => 60 * 60,
        "day" => 60 * 60 * 24,
        "week" => 60 * 60 * 24 * 7,
        "month" => (int)(60 * 60 * 24 * 30.5),
        "year" => (int)(60 * 60 * 24 * 365.25)
    );
    
    if ($multiplier = $units[strtolower($matches[2])]) {
        $seconds = max(0, $matches[1]) * $multiplier;
    }
    return $seconds;
}

$backup = mysql_connect("localhost", "root", "select11");

// Load plot.rb
#$cfg = file_get_contents(ROOT . 'config/initializers/plot.rb');

// "parse" plot.rb
#preg_match("/PLOT = ActiveSupport\:\:JSON\.decode\('({.*})'\)/si", $cfg, $matches);
#$plot = json_decode($matches[1]);

class Plot {
    public $units = array("1 Day", "1 Week", "1 Month", "6 Month", "1 Year", "5 Year"),
           $each_unit = 50;
}
$plot = new Plot();

$last_unit = array_shift($plot->units);
while ($unit = array_shift($plot->units)) {
    $seconds = parse_interval($unit);
       
    echo "\nresolving between $last_unit and $unit\n";
       
    $sql_query = "SELECT created_at " .
                 "FROM experience_changes ".
                 "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $unit) AND " .
                       "created_at <= DATE_SUB(CURDATE(), INTERVAL $last_unit) " .
                 "GROUP BY created_at ".
                 "ORDER BY created_at DESC";
    $result = error_query($sql_query, $db);    
    echo "$sql_query\n";
    
    if (mysql_num_rows($result) > $plot->each_unit) { // more rows than needed
        $skipped = 0;
        $i = 1;
        $every_row = ceil(mysql_num_rows($result) / $plot->each_unit); // keep only 1 row every x rows
        
        while ($date = mysql_fetch_assoc($result)) {
            if ($i % $every_row) {
                // backup the values
                $sql_query = "INSERT INTO backup.experience_changes (user_id, world_id, experience, created_at) ".
                             "SELECT user_id, world_id, experience, created_at FROM stellari_freewar3_" . ENV . ".experience_changes WHERE created_at = '" . $date['created_at'] . "'";
                #error_query($sql_query, $backup);
                
                $sql_query = "DELETE FROM experience_changes WHERE created_at = '" . $date['created_at'] . "'";
                #error_query($sql_query, $db);
                echo $sql_query;
            } else {
                echo "skip";
                $skipped += 1;
            }
            echo "\n";
            $i += 1;
        }
        echo "remaining $skipped rows\n";
    }
           
    $last_unit = $unit;
}//*/

/*
##
# clean world collumn
##
$table = "fwfam_changes_races";

$sql_query = "SELECT * FROM $table WHERE 1 GROUP BY world";
$rows = error_query($sql_query, $db);
while ($row = mysql_fetch_assoc($rows)) {
    if (!is_numeric($row['world'])) {
        $sql_query = "SELECT id FROM worlds WHERE short = '" . $row['world'] . "' OR subdomain = '" . $row['world'] . "'";
        $result = error_query($sql_query, $db);
        if ($world = mysql_fetch_assoc($result)) {
            $sql_query = "UPDATE $table SET world = '" . $world['id'] . "' WHERE world = '" . $row['world'] . "'";
            echo "$sql_query;\n";
        }
    }
}//*/