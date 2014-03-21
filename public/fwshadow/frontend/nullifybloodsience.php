<?php
if (isset($_POST['data'])) {
    $stage_pattern = "\s*\/\/\s*(\d+)";
    
    $data = $_POST['data'];
    preg_match("/^$stage_pattern/", $data, $matches);
    if (empty($matches)) {
        echo 'no stage found<br>';
    } else {
        $stage_gl = (int)$matches[1];
        echo "stage: $stage_gl<br>";
        
        $lines = array_slice(explode("\n", $data), 1);
        for ($i = 0, $length = count($lines); $i < $length; ++$i) {
            $line = $lines[$i];
            //echo "$line<br>";
            
            preg_match("/$stage_pattern\s*$/", $line, $matches);
            
            if (empty($matches)) {
                $stage = $stage_gl;
            } else {
                $stage = (int)$matches[1];
                
                $line = preg_replace("/(.*)$stage_pattern/", "$1", $line);
                //echo "cleard: $line<br>";
            }
            
            $line = preg_replace("/\}.*$/", "}", $line);
            
            if ($stage > 0) {
                preg_match('/"reward":\s*(-?\d+)/', $line, $matches);
                $reward = (int)$matches[1];
                
                if ($reward > 0) {
                    $base = (int)($reward / pow(1.01, $stage));
                    //echo "reward: $reward; base: $base<br>";

                    while ((int)($base * pow(1.01, $stage)) < $reward) {
                        ++$base;
                    }

                    $line = preg_replace('/("reward":)\s*-?\d+/', "$1 $base", $line);
                    //echo $line;
                }
                
                
            }
            
            $lines[$i] = $line;
        }
        
        $data = implode(",\n", $lines);
    }
} else {
    $data = '';
}
?>
<form action="" method="POST">
    <textarea name="data" rows="10" cols="170"><?php echo htmlspecialchars(trim($data),  ENT_COMPAT | ENT_HTML401, 'ISO-8859-1'); ?></textarea>
    <input type="submit">
</form>