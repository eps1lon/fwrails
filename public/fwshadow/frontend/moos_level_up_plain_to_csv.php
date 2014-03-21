<?php
error_reporting(E_ALL);

function parse_plain ($plain, $props) {
    $rows = array_filter(explode("\n", trim($plain)));
    
    $data = array(
        'larinit' => null
    );
    
    preg_match_all("/(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $rows[0], $base_values);
    
    $base_values = array(
        +$base_values[1][0],
        +$base_values[2][0],
        +$base_values[3][0],
        +$base_values[4][0]
    );
    
    //return $data;
    
    $i = 0;
    foreach ($props as $prop => $name) {
        if (!isset($base_values[$i])) {
            echo $plain;
            continue;
        }
        
        $data["{$prop}_gw"] = $base_values[$i];
        $data["{$prop}_add"] = null;
        $data["{$prop}_old"] = null;
        $data["{$prop}_new"] = null;
        
        if (preg_match("/$name: (\d+) \((\+|\-)?(\d+)\)/", $plain, $values)) {
            $data["{$prop}_new"] = (int)$values[1];
            $data["{$prop}_add"] = intval($values[2] . $values[3]);
            $data["{$prop}_old"] = $data["{$prop}_new"] - $data["{$prop}_add"];
        }
        
        $i++;
    }
    
    if (is_null($data['p_new'])) {
        if (preg_match("/Potential (\d+)/i", $plain, $potential)) {
            $data['p_new'] = $data['p_old'] = (int)$potential[1];
        }
    }
    
    if (preg_match("/Du hast (\d+) Larinit-Vorr.te erhalten/i", $plain, $larinit)) {
        $data['larinit'] = (int)$larinit[1];
    }
    
    return $data;
}

function parsed_table_level ($parsed, $prop) {
    $table = "<table id='data_$prop'><tr><th>Grundwert</th><th>Wert</th>";
    if ($prop != "m") {
        $table .= "<th>Mutation alt</th><th>Mutation neu</th>";
    }
    
    $table .= "<th>Zuwachs</th></tr>";
    
    
    foreach ($parsed as $row) {
        if (!is_null($row["{$prop}_add"])) {
            $table .= "<tr><td>{$row["{$prop}_gw"]}</td><td>{$row["{$prop}_old"]}</td>";
            
            if ($prop != "m") {
                $table .= "<td>{$row["m_old"]}</td><td>{$row["m_new"]}</td>";
            }
            
            $table .= "<td>{$row["{$prop}_add"]}</td></tr>";
        }
    }
    
    return $table . "</table>";
}

function parsed_table_potential ($parsed) {
    $table = "<table id='potential'><tr><th>Alt</th><th>Neu</th><th>Larinit</th></tr>";
    
    foreach ($parsed as $row) {
        if (!is_null($row["p_old"]) && !is_null($row["p_new"]) && !is_null($row["larinit"])) {
            $table .= "<tr><td>{$row["p_old"]}</td><td>{$row["p_new"]}</td>".
                      "<td>{$row["larinit"]}</td></tr>";
        }
    }
    
    return $table . "</table>";
}

if (isset($_POST['plain'])) {
    $props = array(
        'a' => 'Anreicherung',
        'w' => 'Wachstum',
        'p' => 'Potential',
        'm' => 'Mutation'
    );
    
    $rows = explode("\n\r", trim($_POST['plain']));
    $parsed = array();
   
    #echo '<pre>' . print_r($rows, true) . '</pre>';
    
    foreach ($rows as $row) {
        $data = parse_plain($row, $props);
        $parsed[] = $data;
        
        #echo '<pre>' . print_r($data, true) . '</pre>';
    }
    
    foreach ($props as $prop => $name) {
        echo "<h2>Auflevel $name</h2>" . parsed_table_level($parsed, $prop);
    }
    
    echo "<h2>Potential</h2>" . parsed_table_potential($parsed);
} else {
    $_POST['plain'] = "";
}
?>
<form action="" method="post">
    <label for="plain">text</label>
    <textarea id="plain" name="plain" rows="10" cols="50"><?= $_POST['plain'] ?></textarea>
    <input type="submit">
</form>