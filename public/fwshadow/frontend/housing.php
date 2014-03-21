<?php

error_reporting(E_ALL);

require '../backend/php/lib/class.Moos.php';

function field_cost($count) {
    #return max(1, floor(log($count))) + floor($count / 100) - 1;
    return floor(max(1, log($count)));
}

function moos_cross_stage($stage1, $stage2) {
    $maxstage = max($stage1, $stage2);
    return $maxstage + floor(($maxstage / 5) / max(abs($stage1 - $stage2), 1));
}

if (isset($_GET['cross'])) {
    $stage1 = (int)$_POST['moos1'];
    $stage2 = (int)$_POST['moos2'];
    $maxstage = max($stage1, $stage2);
    $newstage = moos_cross_stage($stage1, $stage2);
    
    echo "<p>Maxstufe = max(Moosstufe1, Moosstufe1) = $maxstage</p>";
    echo "<p>Neumoosstufe = Maxstufe + Abrunden( ( Maxstufe / 5 ) / Maximum( Betrag( Moosstufe1 - Moosstufe2 ) , 1 ) )</p>";
    echo "<p>Neumoosstufe = $maxstage +  Abrunden( ( $maxstage / 5 ) / Maximum( Betrag( $stage1 - $stage2 ), 1 ) )</p>";
    echo "<p>Neumoosstufe = $maxstage +  Abrunden( " . ($maxstage / 5) . " / max( " . abs($stage1 - $stage2) . ", 1 ) )</p>";
    echo "<p>Neumoosstufe = $maxstage +  Abrunden( " . ($maxstage / 5) . " / " . max(abs($stage1 - $stage2), 1) . " )</p>";
    echo "<p>Neumoosstufe = $maxstage +  Abrunden( " . ($maxstage / 5)  / max(abs($stage1 - $stage2), 1) . " )</p>";
    echo "<p>Neumoosstufe = $maxstage +  " . floor( ($maxstage / 5) / max(abs($stage1 - $stage2), 1) ) . "</p>";
    echo "<p>neue Stufe: $newstage</p>";
} else if (isset($_GET['field_costs'])) {
    $current = (int)$_POST['current'];
    $add = (int)$_POST['add'];
    $sum = 0;
    
    echo "<p>Das Eingangsfeld wird bei der Berechnung nicht berücksichtigt.</p>";
    echo "<ol start='" . ($current + 1) . "'>";
    for ($count = $current; $count < $current + $add; ++$count) {
        $costs = field_cost($count - 1);
        $sum += $costs;
        echo "<li>$costs</li>";
    } 
    echo "</ol><p>Gesamt: $sum</p>";
}
$old_costs = 1;
$start = 2;
$end = $start;
do {
    $costs = field_cost($end);
    
    if ($old_costs < $costs) {
        
        echo "|-<br>| $start -  $end || $old_costs<br>";
        $start = $end + 1;
        $old_costs = $costs;
    }
    
    $end++;
} while ($end <= 41 * 41);
?>
<fieldset>
    <legend>Moose kreuzen</legend>
    <form action="?cross" method="POST">
        <label for="moos1">Moosstufe1</label>
        <input name="moos1" id="moos1" value="<?php echo $stage1; ?>" type="number">
        <label for="moos1">Moosstufe2</label>
        <input name="moos2" id="moos2" value="<?php echo $stage2; ?>" type="number">
        <input type="submit">
    </form>
</fieldset>

<fieldset>
    <legend>Kosten pro Feld</legend>
    <form action="?field_costs" method="POST">
        <label for="current">Aktuelle Feldanzahl (inklusive Eingangsfeld)</label>
        <input name="current" id="current" value="<?php echo $current; ?>" type="number">
        <label for="add">Wieviel Felder sollen erstellt werden?</label>
        <input name="add" id="add" value="<?php echo $add; ?>" type="number">
        <input type="submit">
    </form>
</fieldset>
