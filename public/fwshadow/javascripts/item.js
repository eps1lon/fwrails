(function (window) {
    'use strict';
    //*
    var quickjump = JSON.parse(localStorage.getItem('slmania-quickjump')) || {},
        teleporter =  {
            'gzk':   {
                items: ['gepresste Zauberkugel', 'Schattenflügel', 'Nebel der Teleportation'],
                waypoints: {
                    "2": "Konlir",
                    "68": "Anatubien",
                    "73": "Bank",
                    "87": "Reikan",
                    "110": "Tal der Ruinen",
                    "169": "vergessenes Tal",
                    "290": "Mentoran",
                    "387": "Narubia",
                    "437": "Nawor",
                    "538": "Buran",
                    "816": "Sutranien",
                    "884": "Hewien",
                    "988": "Orewu",
                    "1079": "Casino von Ferdolien",
                    "1321": "Kanobien",
                    "1715": "Terasi",
                    "4304": "Lodradon"
                }
            },
            'gelbe':  {
                items: ['geklebte Zauberkugel', 'gelbe Zauberkugel'],
                waypoints: {
                    
                }
            },
            'poma': {
                items: ['rote Portalmaschine', 'Portalmaschien'],
                waypoints: {
                    
                }
            }
        };
    
    if (window.location.href.match(/action=watch/)) {
        var watchitem = $('.listcaption').text().trim();
        
        $.each(teleporter, function (name, data) {
            var items = data.items;
            
            if ($.inArray(watchitem, items) !== -1) {
                $('<a/>', {
                    click: function () {
                        quickjump[name] = {
                            item: watchitem,
                            item_id: $('a[href*="act_item_id"]:first').attr('href').match(/act_item_id=(\d+)/)[1]
                        }
                        //console.log(quickjump);
                        
                        localStorage.setItem('slmania-quickjump', JSON.stringify(quickjump));
                    },
                    href: '#',
                    html: '[Als ' + name + ']'
                }).appendTo('.listcaption')
                
                return false; // break loop
            }
        });
    } else if ($('#listrow_aka_battlep').length) { // action === ""
        $.each(quickjump, function (name, data) {
            $('<div>', {
                id: 'slmania-quickjump-' + name
            }).insertAfter('#listrow_status + div');
            
            // waypoints
            $.each(teleporter[name].waypoints, function (value, place) {
                $('<a/>', {
                    click: function () {
                        $('<form>').attr('action', 'item.php?action=activate&act_item_id=' + quickjump[$(this).data('teleporter')].item_id).
                                    attr('method', 'POST').
                                    html('<input type="hidden" name="z_pos_id" value="' + $(this).data('waypoint_val') + '">').
                                    submit();
                    },
                    href: '#',
                    html: place.slice(0, 1)
                }).attr('data-teleporter', name).
                   attr('data-waypoint_val', value).
                   addClass('slmania-quickjump-waypoint').
                   appendTo('#slmania-quickjump-' + name);
            });
            
            // get new item link
            $('<a/>', {
                click: function () {
                    var name = $(this).data('teleporter');
                    
                    // alle zauber anzeigen
                    $.get(
                        'http://welt3.freewar.de/freewar/internal/item.php', 
                        {'action': 'openinv', 'selcat': 5}, 
                        function (data) {
                            $('<div/>').html(data).find('.listitemrow').each(function (i, itemrow) {
                                // durchlaufen und passendes item finden
                                if ($('b', itemrow).text().trim() === quickjump[name].item) {
                                    quickjump[name].item_id = $('a[href*="act_item_id"]:first', itemrow).attr('href').match(/act_item_id=(\d+)/)[1]
                                    
                                    //console.log(itemrow, quickjump);
                                    localStorage.setItem('slmania-quickjump', JSON.stringify(quickjump));
                                }
                            });
                            
                            // und schließen
                            $.get('http://welt3.freewar.de/freewar/internal/item.php', 
                                  {'action': 'closeinv', 'selcat': 1});
                        }
                    );
                },
                href: '#',
                html: '®'
            }).attr('data-teleporter', name).appendTo('#slmania-quickjump-' + name)
        });
    }
    
    //console.log(quickjump);//*/
}(window));