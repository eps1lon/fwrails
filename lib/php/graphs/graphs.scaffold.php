<?php
if (!defined('GRAPH_INCLUDED')) {
    echo "no graph library included!";
    exit;
}

// get margin
list($lm, $tm, $rm, $bm) = $margin;

/* legend vertical, right
$legend_width = 10 + max(array_map("strlen", $legend)) * 8;
$margin['right'] += $legend_width;//*/

//* legend vertical, bottom
$legend_height = (count($legend) + 1) * 20; // legend rows + thead
//                    legend height   scale
$bm += $legend_height + 18;//*/

if ($graph_dimensions_mode === DIMENSIONS_FIXED_IMAGE) {
    // default jpgraph behvior
} else if ($graph_dimensions_mode === DIMENSIONS_FIXED_GRAPH) {
    // calculate actual dimensions
    $width += $lm + $rm;
    $height += $tm + $bm;
}

// set dimensions
$graph = new Graph($width, $height);
$graph->graph_theme = null;
#$graph->SetMarginColor('khaki:0.6'); 
$graph->SetMargin($lm, $rm, 
                  $tm, $bm);

// title
$graph->title->set($title);
$graph->subtitle->set($subtitle);

// set scale
$graph->SetScale('datlin');

// Axis
$graph->SetTickDensity(TICKD_DENSE);

// xaxis
$graph->xaxis->scale->SetDateFormat($date_format);
#$graph->xaxis->SetLabelAngle(30);
#$graph->xaxis->scale->ticks->SupressFirst(); 
$graph->xaxis->scale->ticks->SupressTickMarks(false);

// yaxis
$graph->yaxis->SetLabelFormatCallback('number_in_magnitudes');

$xvalues = [];

// stats
$stats_head = ["name", "cur", "min", "max", "avg"];
$stats_table = new GTextTable();
$stats_table->init(count($legend) + 1, count($stats_head));
$stats_table->SetAlign("right");
$stats_table->SetColAlign(0, "center"); // name
$stats = [0 => $stats_head];
// width
$col_name_width = 250;
$stats_table->SetMinColWidth(0, $col_name_width);
// col width
if ($graph_dimensions_mode === DIMENSIONS_FIXED_GRAPH) {
    $col_width = (int)(($width - $lm - $rm - $col_name_width) / (count($stats_head) - 1));
} else {
    // ?
}
for ($col = 1, $length = count($stats_head); $col < $length; ++$col) {
    $stats_table->SetMinColWidth($col, $col_width);
}
// pos
$stats_table->SetPos($lm, $height - $margin[3]);
$stats_table->SetAnchorPos('left','bottom'); 

// add plots
$row = 1;
foreach ($data as $legend_key => $group_data) {
    $datay = array_values($group_data);
    $datax = array_keys($group_data);
    $xvalues = array_merge($xvalues, $datax);

    // create plot
    $plot = new LinePlot($datay, $datax);
    
    // legend
    $plot->setLegend($legend[$legend_key]);
     
    $graph->add($plot);
    
    #print_r(array_flip($stats_head));
    #print_r(data_stats($group_data) + ["name" => ""]);
    #print_r(array_merge(array_flip($stats_head), data_stats($group_data) + ["name" => ""]));
    $stats[$row] = array_values(array_merge(array_flip($stats_head), 
                                            array_map(function($n) {
                                                return number_in_magnitudes($n, 3);
                                            }, data_stats($group_data)) + ["name" => ""]));
    
    $row++;
}

// stats
$stats_table->Set($stats);
$graph->add($stats_table);

if (!empty($xvalues)) {
    $xmax = max($xvalues);
    $xmin = min($xvalues);
    $graph->xaxis->scale->ticks->Set(($xmax - $xmin) / ($graph_tick_count_major - 1),
                                     ($xmax - $xmin) / ($graph_tick_count_minor - 1));
}

// legend
$graph->legend->SetAbsPos($lm, $height - $margin[3] - 2, "left", "bottom");
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->SetFillColor("white");
$graph->legend->SetFrameWeight(0);
$graph->legend->SetVColMargin(6);

// draw
$graph->stroke();