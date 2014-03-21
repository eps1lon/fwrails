<?php
header('Content-Type: text/plain; charset=utf-8;');
error_reporting(0);

set_time_limit(5);

define("EPSILON", pow(10, -5));

require '../backend/php/lib/shop_init.php';

function get_shop($shops, $x, $y) {
    foreach ($shops as $shop) {
        if ($shop->x == $x && $shop->y == $y) {
            return $shop;
        }
    }
    
    return null;
}

function shop_positions($shops) {
    $positions = array();
    
    foreach ($shops as $shop) {
        $positions[] = array(
            'name' => $shop->name,
            'x' => $shop->x,
            'y' => $shop->y
        );
    }
    
    return $positions;
}

function utf8_urldecode($str) {
    $str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
    return html_entity_decode($str,null,'UTF-8');;
}

$json = array();

if (isset($_GET['do'])) {
    $raw_shops = $shops;
        
    $raw_shops['Belpharia - Unterkunft'] = $belpha;
    $raw_shops['Azul - Plunderladen'] = $plunder;
    $raw_shops['Laree - Zubehörladen'] = $laree;
    
    if ($_GET['do'] == 'full') {
        $json['shops'] = array();
        
        foreach ($raw_shops as $name => $shop) {
            $shop->name = $name;
            $json['shops'][] = $shop->__serializeable();
        }
        
        usort($json['shops'], function ($a, $b) {
            return strcasecmp($b['sellfactor'], $a['sellfactor']);
        });
    } elseif ($_GET['do'] == 'lookfor_item') {
        if (isset($_GET['item_name'])) {
            $item_shops = array_filter($raw_shops, function ($shop) {
                return is_array($shop->get_items());
            });
            $shop = null;
            $item_name = $_GET['item_name'];
            
            foreach ($item_shops as $item_shop) {
                if (in_array($item_name, $item_shop->get_items())) {
                    $shop = $item_shop;
                    break;
                }
            }
            
            if (is_null($shop)) {
                $json['error'] = "no variable itemshop found that contains `$item_name`";
            } else {
                //  lookfor the item
                $last_found = $shop->lookfor_item($_GET['item_name']);
                
                // set shop to this time
                $shop->forecast_time($last_found);
                
                if (isset($_GET['buyfactor'])) { // seek for specific buyfactor
                    $buyrange = $shop->get_buyrange();
                    $buyfactor = max($buyrange[0] / 100, min($buyrange[1] / 100, (float)$_GET['buyfactor']));
                    
                    while ($shop->buyfactor() >= $buyfactor + EPSILON) {
                        // move pointer to next interval
                        $shop->forecast_time($last_found + $shop->get_interval());
                        
                        // look for that item from this interval
                        $last_found = $shop->lookfor_item($_GET['item_name']);
                        
                        // and try again
                        $shop->forecast_time($last_found);
                    }
                }
                
                $json['shop_lookfor'] = $shop->__serializeable();
                #echo date(DATE_RSS, $shop->get_time());
            }
        } else {
            $json['error'] = "no item given; Usage: `shop_api.php?do=lookfor_item&item_name=([a-z\-]+)(&buyfactor=(\d\.\d+))?`";
        }
    } else if ($_GET['do'] == 'lookfor_buyfactor') {
        /*
        $shop = get_shop($raw_shops, $_GET['shop_x'], $_GET['shop_y']);
        
        if (is_null($shop)) {
            $json['error'] = "no shop at '" . $_GET['shop_x'] . "'/'" . $_GET['shop_y'] . "'";
            $json['possible_shops'] = shop_positions($raw_shops);
        } else {
            if (isset($_GET['buyfactor'])) {
                $buyrange = $shop->get_buyrange();
                $buyfactor = max($buyrange[0] / 100, min($buyrange[1] / 100, (float)$_GET['buyfactor']));

                $shop->forecast_time($shop->lookfor_buyfactor($buyfactor));

                $json['shop_lookfor'] = $shop->__serializeable();
            } else {
                $json['error'] = "no buyfactor given; Usage: `shop_api.php?do=lookfor_buyfactor&shop_x=%X-Position%&shop_y=%Y-Position%&buyfactor=%Faktor%`";
            }
            
        }//*/
        
        if (isset($_GET['buyfactor'])) {
            $loop_shops = array();
            
            $buyrange = $shop->get_buyrange();
            $buyfactor = max($buyrange[0] / 100, min($buyrange[1] / 100, (float)$_GET['buyfactor']));
            
            if (isset($_GET['shop_x']) && isset($_GET['shop_y'])) {
                $shop = get_shop($raw_shops, $_GET['shop_x'], $_GET['shop_y']);
                
                if (is_null($shop)) {
                    $json['error'] = "no shop at " . var_dump($_GET['shop_x']) . "/" . var_dump($_GET['shop_y']);
                    $json['possible_shops'] = shop_positions($raw_shops);
                } else if ($shop->sells_items() === false) {
                    $json['error'] = "cant buy at this shop";
                } else {
                    $loop_shops[] = $shop;
                }
            } else {
                foreach ($raw_shops as $shop) {
                    if ($shop->sells_items() === true) {
                        $loop_shops[] = $shop;
                    }
                }
            }
            
            if (!empty($loop_shops)) {
                $json['shops'] = array();
                
                foreach ($loop_shops as $shop) {
                    $json['shops_current'][] = $shop->__serializeable();
                    
                    $shop->forecast_time($shop->lookfor_buyfactor($buyfactor));

                    $json['shops'][] = $shop->__serializeable();
                }
                
                usort($json['shops'], function ($a, $b) {
                    return strcasecmp($a['time'], $b['time']);
                });
            }
        } else {
            $json['error'] = "no buyfactor given; Usage: `shop_api.php?do=lookfor_buyfactor&buyfactor=%Faktor%(&shop_x=%X-Position%&shop_y=%Y-Position%)`";
        }
    } else if ($_GET['do'] == 'lookfor_sellfactor') {
        if (isset($_GET['sellfactor'])) {
            $loop_shops = array();
            
            $sellrange = $shop->get_sellrange();
            $sellfactor = (float)$_GET['sellfactor'];
            
            if (isset($_GET['shop_x']) && isset($_GET['shop_y'])) {
                $shop = get_shop($raw_shops, $_GET['shop_x'], $_GET['shop_y']);
                
                if (is_null($shop)) {
                    $json['error'] = "no shop at " . var_dump($_GET['shop_x']) . "/" . var_dump($_GET['shop_y']);
                    $json['possible_shops'] = shop_positions($raw_shops);
                } else if ($shop->buys_items() === false) {
                    $json['error'] = "cant sell items at this shop";
                } else {
                    $loop_shops[] = $shop;
                }
            } else {
                foreach ($raw_shops as $shop) {
                    if ($shop->buys_items() === true) {
                        $loop_shops[] = $shop;
                    }
                }
            }
            
            if (!empty($loop_shops)) {
                $json['shops'] = array();
                
                foreach ($loop_shops as $shop) {
                    $json['shops_current'][] = $shop->__serializeable();
                    
                    $shop->forecast_time($shop->lookfor_sellfactor($sellfactor));

                    $json['shops'][] = $shop->__serializeable();
                }
                
                usort($json['shops'], function ($a, $b) {
                    return strcasecmp($a['time'], $b['time']);
                });
            }
        } else {
            $json['error'] = "no sellfactor given; Usage: `shop_api.php?do=lookfor_sellfactor&sellfactor=%Faktor%(&shop_x=%X-Position%&shop_y=%Y-Position%)`";
        }
    } else if ($_GET['do'] == 'get_shops') {
        $json['shops'] = shop_positions($raw_shops);
    } else if ($_GET['do'] == 'get_shop') { 
        if ($shop = get_shop($raw_shops, $_GET['shop_x'], $_GET['shop_y'])) {
            if (isset($_GET['intervall'])) {
                $shop->forecast_intervall((int)$_GET['intervall']);
            } else if (isset($_GET['time'])) {
                $shop->forecast_time((int)$_GET['time']);
            }
        }
        
        $json['shop'] = $shop->__serializeable();
    } else {
        
    }
} else {
    $json['error'] = "Usage:\nbuyfactor/sellfactor as `Number[%] / 100`\n".
                     "Get all Shops with their Position `shop_api?do=get_shops`\n".
                     "Get all Shops with buy- and sellfactor and their specific items `shop_api.php?do=full`\n".
                     "Look for a certain item, buyfactor optional (search inteligent) `shop_api.php?do=lookfor_item&item_name=%Beispielitem%(&buyfactor=%Faktor%)?`\n".
                     "Look for a certain buyfactor at a specific shop `shop_api.php?do=lookfor_buyfactor&shop_x=%X-Position%&shop_y=%Y-Position%&buyfactor=%Faktor%`\n".
                     "Look for a certain sellfactor, specific shop optional `shop_api.php?do=lookfor_sellfactor&sellfactor=%Faktor%(&shop_x=%X-Position%&shop_y=%Y-Position%)`";
}

if (isset($json['shop_lookfor'])) {
    #echo date(DATE_RSS, $json['shop_lookfor']['time']);
}

echo json_encode($json, (isset($_GET['debug']) ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0));
