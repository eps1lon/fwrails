<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
<?php
if (isset($_POST['bloodsample_plain'])) {
    $bloodstage = 50;
    
    $bloodsample_plain = $_POST['bloodsample_plain'];
    
    $lines = explode("\n", $bloodsample_plain);
    $data = array();
    $line_data = false;
    $bonus_offset = 1;
    
    for ($i = 0, $count = count($lines); $i < $count; ++$i) {
        $line = $lines[$i];
        
        if (preg_match("/perfekten Blutprobe eine zusätzliche Belohnung von (\d+) Goldmünzen und (\d+) Auftragspunkt(en)?./i", $line, $bonus)) {
            $data[$i-$bonus_offset]['bonus'] = array((int)$bonus[0], (int)$bonus[1]);
            $bonus_offset++;
        } else {
            $line_data = array(
                'reward' => null,
                'bonus' => null,
                'special' => 0,
                'name' => null,
                'date' => null
            );

            if (preg_match("/verkauft eine Blutprobe von (.*) für (\d+) Goldmünzen und (\d+) Auftragspunkte/i", $line, $chattext)) {
                
                $line_data['name'] = $chattext[1];
                $line_data['reward'] = (int)$chattext[2];
                
                $line_data['special'] = (int)($chattext[3] == 5);
            } else if (preg_match("/geistlosen Wesen (.*?)\./i", $line, $chattext)) {
                $line_data['name'] = $chattext[1];
                
                if (preg_match("/(\d+) Goldmünzen/i", $line, $reward)) {
                    $line_data['reward'] = (int)$reward[0];
                }
                
                if (preg_match("/(\d+) Auftragspunkte/i", $line, $mission_points)) {
                    $line_data['special'] = (int)($mission_points[0] == 5);
                }
            }
            
            if (!is_null($line_data['reward']) || true) {
                $reward = $line_data['reward'];
                
                $base = (int)($reward / pow(1.01, $bloodstage));
                //echo "reward: $reward; base: $base<br>";

                while ((int)($base * pow(1.01, $bloodstage)) < $reward) {
                    ++$base;
                }
                
                $line_data['reward'] = $base;
            }
            
            if (preg_match("/\. (\d+)/i", $line, $date)) {
                $line_data['date'] = (int)$date[1];
            }
            
            if (preg_match("/%(dungeon|surface)%/i", $line, $sector)) {
                $line_data['dungeon'] = strtolower($sector[1]) == "dungeon";
            }
            
            #echo '<pre>' . print_r($line_data, true) . '</pre>';
            $data[] = $line_data;
        }
        
        
    }
    #echo '<pre>' . print_r($data, true) . '</pre>';
   
    echo implode(",\n<br>", array_map(function ($line) {
        return json_encode($line, JSON_UNESCAPED_UNICODE);
    }, $data));
    #echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
?>
<form method="POST">
    <label for="bloodsample_plain">Plain Text</label>
    <textarea id="bloodsample_plain" name="bloodsample_plain" rows="10" cols="70"><?php echo $bloodsample_plain; ?></textarea>
    <input type="submit">
</form>
</body></html>
