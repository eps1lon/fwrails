<?php
if (!defined('GRAPH_INCLUDED')) {
    echo "no graph library included!";
    exit;
}

if ($graph_dimensions_mode === DIMENSIONS_FIXED_IMAGE) {
    // default jpgraph behvior
} else if ($graph_dimensions_mode === DIMENSIONS_FIXED_GRAPH) {
    // calculate actual dimensions
    $width += $margin['left'] + $margin['right'];
    $height += $margin['top'] + $margin['bottom'];
}

// 
$legend_width = 10 + max(array_map("strlen", $legend)) * 8;
$margin['right'] += $legend_width;

// set dimensions
$graph = new Graph($width, $height);
$graph->setMargin($margin['left'], $margin['right'], 
                  $margin['top'], $margin['bottom']);

// title
$graph->title->set($title);
$graph->subtitle->set($subtitle);

// set scale
$graph->SetScale('datlin');

// xaxis
$graph->xaxis->scale->SetDateFormat($date_format);
$graph->xaxis->SetLabelAngle(30);

// yaxis
$graph->yaxis->SetLabelFormatCallback('number_in_magnitudes');

// add plots
foreach ($data as $legend_key => $group_data) {
    $datay = array_values($group_data);
    $datax = array_keys($group_data);
   
    // create plot
    $plot = new LinePlot($datay, $datax);
    
    // legend
    $plot->setLegend($legend[$legend_key]);
    
    $graph->add($plot);
}

// legend
$graph->legend->SetShadow('gray@0.4', 1);
$graph->legend->SetAbsPos(0,
                          $margin['top'], 'right', 'top');

// draw
$graph->stroke();