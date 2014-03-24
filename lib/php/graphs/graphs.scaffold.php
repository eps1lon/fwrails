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

// Axis
$graph->SetTickDensity(TICKD_DENSE);

// xaxis
$graph->xaxis->scale->SetDateFormat($date_format);
$graph->xaxis->SetLabelAngle(30);
#$graph->xaxis->scale->ticks->SupressFirst(); 
$graph->xaxis->scale->ticks->SupressTickMarks(false);

// yaxis
$graph->yaxis->SetLabelFormatCallback('number_in_magnitudes');

$xvalues = [];

// add plots
foreach ($data as $legend_key => $group_data) {
    $datay = array_values($group_data);
    $datax = array_keys($group_data);
    $xvalues = array_merge($xvalues, $datax);

    // create plot
    $plot = new LinePlot($datay, $datax);
    
    // legend
    $plot->setLegend($legend[$legend_key]);
    
    $graph->add($plot);
}

if (!empty($xvalues)) {
    $xmax = max($xvalues);
    $xmin = min($xvalues);
    $graph->xaxis->scale->ticks->Set(($xmax - $xmin) / ($graph_tick_count_major - 1),
                                     ($xmax - $xmin) / ($graph_tick_count_minor - 1));
}

// legend
$graph->legend->SetShadow('gray@0.4', 1);
$graph->legend->SetAbsPos(0, $margin['top'], 'right', 'top');
$graph->legend->SetLayout(LEGEND_VERT);

// draw
$graph->stroke();