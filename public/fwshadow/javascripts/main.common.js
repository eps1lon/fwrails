/*jslint devel: true, browser: true, continue: true, nomen: true, regexp: true, maxerr: 50, indent: 4 */
/**
 * fill in your personal user_id
 */
var user_id = '721366d55104bb1765173fe40895a29cf76c7dae',
    SLMANIA_URL = 'http://fwrails.net/fwshadow/';//'http://localhost/ror/freewar3/public/fwshadow/';

(function (window, __undefined) {
    "use strict";
    var $ = window.jQuery,
        alarm = !!JSON.parse(localStorage.getItem('slmania-alarm')),
        alarm_observer = (function () {
            var timeout_id = -1; // static

            return function (clear) {
                if (timeout_id === -1) {
                    timeout_id = window.setTimeout(function () {
                        this.location.href = this.location.href;
                    }, 10000);
                } else if (clear === true) {
                    window.clearTimeout(timeout_id);
                    timeout_id = -1;
                }
            };
        }()),
        data = (function () {
            var key_ = function (key) {
                return ['slmania', key].join('-');
            };
            
            return {
                get: function (key, init) {
                    return (JSON.parse(localStorage.getItem(key_(key))) || init);
                },
                save: function (key, data) {
                    return localStorage.setItem(key_(key), JSON.stringify(data));
                }
            }
        })(),
        attacked_npc,
        container = null,
        drops = [],
        hint_msg = '',
        old_items = JSON.parse(localStorage.getItem('slmania-items')) || {},
        items = {},
        item_id = 0,
        last_sample_id = -1, // für window clearInterval
        log = JSON.parse(localStorage.getItem('slmania-log')) || [],
        log_msg = '',
        matches = [],
        old_npcs = JSON.parse(localStorage.getItem('slmania-npcs')) || {},
        npcs = {},
        params = $SERVER.params,
        passages = data.get('passages', []),
        place = null,
        unique_npcs = {
            "": -1,
            "NPC": 0,
            "Unique-NPC": 1,
            "Gruppen-NPC": 2
        },
        wanted_npc = localStorage.getItem('slmania-npc_wanted'),
        house = localStorage.getItem('slmania-house') || null;
    
        
    //* Baumenü
    $('div.editcaption a:last').replaceWith(
        '<a href="main.php?do_eval=rescue2">Eingang</a> / '+
        '<a href="main.php?do_eval=editplace">Text</a> / '+
        '<a href="main.php?do_eval=image">Bild</a> / '+
        '<a href="main.php?do_eval=image&light=yes">Licht</a> / '+
        '<a href="main.php?do_eval=addplace">Größer</a> / '+
        '<a href="main.php?do_eval=delplace">Kleiner</a> / '+
        '<a href="main.php?do_eval=placeitems2">Aufstellen</a> / '+
        '<a href="main.php?do_eval=placeitems3">Lagern</a> / '+
        '<a href="main.php?do_eval=placeitems1">Verschieben</a> / '+
        '<a href="main.php?do_eval=showmap">Karte</a> / '+
        '<a href="main.php?do_eval=manage">Menü</a>'
    );//*/
    
    // tablesorter larinit-moos-zucht
    if ($.each($('table.mosstable'), function () {
        var $table = $(this),
            // This gets the first <tr> within the table, and remembers it here.
            $headRow = $('tr:nth-of-type(2)', $table).first();
        
        $('tr:nth-of-type(1)', $table).remove();
        $headRow.remove();
        
        if (!$table.has('tbody')) {
            var $otherRows = $('tr', $table);
            $otherRows.remove();
        
            var $tbody = $('<tbody>');
            $table.append($tbody);
            $tbody.append($otherRows);
        }
        
        var $thead = $('<thead>');
        $table.prepend($thead);
        $thead.append($headRow);
        
        $table.tablesorter({
            debug: false
        });
    }));

    // get position
    container = $('table.areadescription');
    if (container.length > 0) {
        place = {};

        //place.desc = $('td.areadescription', container).text().trim();

        container = $('td.mainheader', container);
        place.name = container.text().trim();

        // id := mainmapx(\d+)y(\d+)
        matches = $('img', container)[0].id.match(/x([0-9\-]+)y([0-9\-]+)/);

        if (matches !== null) {
            place.x = +matches[1];
            place.y = +matches[2];
        } else {
            place.x = -10;
            place.y = -10;
        }
    }
    
    localStorage.setItem('slmania-place', place);

    /*
    if (place.x >= -178 && place.x <= -165 && place.y >= -318 && place.y <= -310) { // Finstereishöhle
        var field_id = -1,
            messures = {
                topLeftX: -178,
                topLeftY: -318,
                bottomRightX: -165,
                bottomRightY: -310
            },
            now = Math.floor($.now() / 1000),
            field_id_to_place = function (field_id, messures) {
                var width  = Math.abs(messures.topLeftX - messures.bottomRightX) + 1,
                    height = Math.abs(messures.topLeftY - messures.bottomRightY) + 1,
                    x, y;
                
                y = messures.topLeftY + Math.ceil(field_id / width);
                x = messures.topLeftX + field_id % width;
                
                    
                return {x: x, y: y};
            },
            place_to_field_id = function (place, messures) {
                var width  = Math.abs(messures.topLeftX - messures.bottomRightX) + 1,
                    height = Math.abs(messures.topLeftY - messures.bottomRightY) + 1;
                 
                return Math.abs(messures.topLeftY - place.y) * width
                      + Math.abs(messures.topLeftX - place.x);
            },
            symbol_match = $('a[href*="arrive_eval=oben333"]').text().match(/ (.*) Symbol/),
            symbol = symbol_match === null ? null : symbol_match[1],
            symbols = JSON.parse(localStorage.getItem('slmania-symbols')) || {};
            
            field_id = place_to_field_id(place, messures);
            symbols = symbols;
            
            console.log(field_id, symbols);
            
            if (symbols[field_id] === __undefined) {
                symbols[field_id] = [{'name': null, time: now}];
                
                localStorage.setItem('slmania-symbols', symbols);
            }
            
            if (symbols[field_id][symbols[field_id].length - 1]['name'] != symbol) {
                symbols[field_id].push({'name': symbol, time: now});
                
                localStorage.setItem('slmania-symbols', symbols);
            }
            
            console.log(symbols);
    }//*/
    
    if (place.x <= -51000 + 20 && place.x >= -51000 - 499 * 43 + 20) {        
        (function () {
            var id_offset = 0,
                id_start = 0,
                offset_x = Math.round((-place.x - 51000) / 43),
                offset_y = Math.round((-place.y - 51000) / 43);
            
            id_start = offset_y * 500;
            id_offset = offset_x;
            
            house = id_start + id_offset;
        })();
        
        if (params.do == 'go') {
            passages.push({'from': place, 'to': null, 'via': params});
            data.save('passages', passages);
        }
    } else {
        house = null;
    }
    
    localStorage.setItem('slmania-house', house);
    
    if (house !== null) {
        $('a[href*="do=showitem"] + a[href*="do2="]').each(function (i, link) {
            var $link = $(link),
                params = {};
                
            $($link.attr('href').split("?")[1].split("&")).each(function (i, param) {
                var split = param.split("=");
                params[split[0]] = split[1];
            });
                
            if (params.itemid && params.do2) {
                $link.after(' - ', $('<a/>', {
                    href: $link.attr('href').split("?")[0] + "?do=putitem&arrive_eval=" + params.do2 + "&itemid=" + params.itemid,
                    html: 'Füllen'
                }));
            }
        });
    }

    if (params['do'] == 'guess') {
        (function (form) {
            var combinations = JSON.parse(localStorage.getItem('slmania-combinations')) || {},
            enter_combination = [],
            last_combination = [],
            saved_combination = combinations[house];
            
            
            if (form.length) { // not solved
                $('table tr:nth-child(4) td[align]', form).each(function (i, cell) {
                    last_combination.push(+$(cell).text().trim());
                });

                if (last_combination.length) {
                    enter_combination = last_combination;
                } else {
                    enter_combination = saved_combination || [];
                }

                if (enter_combination.length) {
                    $('select', form).each(function (i, select) {
                        // unselect
                        $('option', select).removeAttr('selected');

                        // select
                        $('option:nth-child(' + (enter_combination[i] + 2) + ')', select).attr('selected', 'selected');
                    });
                }
            } else { // solved
                // find combination
                $('a[name="ucode"]').nextAll().each(function (i, html) {
                    var combination = [];
                    
                    if (html.tagName.toLowerCase() === 'b') { // found
                        
                        // get each number
                        $.each($(html).text().trim().split(""), function (i, number) {
                            combination.push(+number);
                        });
                        
                        // save
                        combinations[house] = combination;
                        localStorage.setItem('slmania-combinations', JSON.stringify(combinations));
                        
                        return false;
                    }
                });
            }
            
            
        }($('form[name="form1"][action*="do=guess#ucode"]:first')));
    }

    // check if moved via passage
    if (passages.length && passages[passages.length - 1].to === null) {
        var diff = {
                x: Math.abs(passages[passages.length - 1].from.x - place.x),
                y: Math.abs(passages[passages.length - 1].from.y - place.y)
            };
        if (diff.x > 1 || diff.y > 1) {
            passages[passages.length - 1].to = place;
        } else if (diff.x == 1 || diff.y == 1) { // just moved
            passages.splice(passages.length - 1, 1);
        }
        
        data.save('passages', passages);
    }
    
    container = $('p.listusersrow');
    if (container.length > 0) {
        container.each(function (i, row) {
            var idlink = null,
                id,
                name,
                strength,
                live,
                unique,
                content,
                matches;

            idlink = $('a.fastattack', row)[0];
            //console.log(idlink);
            if (idlink) { // npc            
                content = $('td:last', row);
                if (content.length === 0) { // no npc-bild
                    content = $(row);
                }
                id = parseInt(idlink.href.slice(idlink.href.search('act_npc_id=')).replace(/act_npc_id=/, ''), 10);

                name = $('b:first', content).text().trim();

                matches = content.text().match(/Angriffsst.rke: (\d+)/);
                if (matches !== null) {
                    strength = +matches[1];
                } else {
                    strength = -1;
                }

                matches = content.text().match(/LP: ([0-9\.]+)\/([0-9\.]+)/);
                if (matches !== null) {
                    live = +(matches[2].replace(/\./, ''));
                } else {
                    live = -1;
                }

                matches = content.text().match(/\(((Gruppen-|Unique-|)NPC)\)/);

                if (matches !== null) {
                    unique = unique_npcs[matches[1]];
                } else {
                    unique = 0;
                }

                //console.log(id, name, strength, live);
                npcs[id] = {
                    name: name,
                    strength: strength,
                    live: live,
                    unique: unique,
                    place: place
                };
            }
        });
    }

    if ($('p.personlistcaption').length > 0) { // action === "" > anzeige der npcs
        // schauen welches npc angegriffen wurde 

        // npcs dauerhaft speichern
        localStorage.setItem('slmania-npcs', JSON.stringify(npcs));
    }

    container = $('p.listplaceitemsrow');
    if (container.length > 0) {
        container.each(function (i, row) {
            var name = $('b:first', row).text().trim(),
                id = $('a:first', row)[0].href.match(/act_item_id=(\d+)/)[1];

            items[id] = name;
        });
    }

    if ($('p.itemlistcaption').length > 0) { // action === "" > anzeige der items
        // items dauerhaft speichern
        localStorage.setItem('slmania-items', JSON.stringify(items));
        
        /* Alles nehmen
        if ($.isEmptyObject(items) === false) {
            $('p.itemlistcaption').html($('<a/>', {
                href: 'javascript: ;',
                html: $.map(items, function (n, i) {
                    return i;
                }).length + ' Items an diesem Ort [Alle nehmen]',
                click: function (e) {
                    var items = $('.listplaceitemsrow a'),
                        len = items.length;
                                       
                    items.each(function (i, link) {
                        $.get($(link).attr('href'), null, function () {
                            len--;

                            if (len === 0) {
                                window.location.href = "?yscroll=" + window.pageYOffset;
                            }
                        });
                    });
                }
            }));
        }//*/
    }
    
    //console.log('params: ', params);
    if (['slapnpc', 'attacknpc', 'chasenpc'].find(params.action) !== -1) { // NPC angegriffen
               
        attacked_npc = old_npcs[params.act_npc_id];
        //console.log('attacked: ', attacked_npc, npcs[params['act_npc_id']]);
        
        if (attacked_npc && !npcs[params.act_npc_id]) { // npc vernichtet
            for (item_id in items) {
                if (!old_items[item_id]) {
                    drops.push(items[item_id]);
                }
            }

            if (+params.act_npc_id === 20892 && drops.length === 0) {
                hint_msg = attacked_npc.name + ' ignoriert';
            } else {
                log_msg = {
                    id: +params.act_npc_id,
                    npc: attacked_npc,
                    drops: drops
                };
                
                switch (params.action) {
                    case 'chasenpc':
                        log_msg.action = 'chase';
                        break;
                    default:
                        log_msg.action = 'kill';
                        break;
                }

                hint_msg = attacked_npc.name + ' erfasst';
            }

        }
    } else if (params.action === 'take') { // Pflanze geerntet
        var item = old_items[params.act_item_id];
        
        if (item) {
            log_msg = {
                id: +params.act_item_id,
                item: item,
                place: place
            };

            hint_msg = item + ' erfasst';
        }
    }

    if (localStorage.getItem('slmania-active') && log_msg !== '') { // es wurde was geloggt
        log.push(log_msg);
        localStorage.setItem('slmania-log', JSON.stringify(log));
    }
    //console.log('log:', log);

    /*
    console.log('Items: ', old_items, items);
    console.log('NPCs: ', old_npcs, npcs);
    console.log('place: ', place);
    */

    // Ort Verarbeitung 
    if (place !== null) {
        if (place.x === 78 && place.y === 93) { // Sumpfwesen
            wanted_npc = $('table.areadescription td.areadescription b:first').text().trim();
            //console.log('searched: ', wanted_npc);
            //wanted_npc = "Glas-Graustein-Bär";
            //*
            //console.log(localStorage.getItem('slmania-npc_wanted'));
            if (wanted_npc != localStorage.getItem('slmania-npc_wanted')) { // neues NPC gesucht
                localStorage.setItem('slmania-npc_wanted', wanted_npc); // NPC zwischenspeichern

                if (wanted_npc) { // npc wird gesucht
                    // mögliche NPC suchen
                    marked_npc.add('bloodsamples', 'FF0000', null, {name: wanted_npc});

                    //*/ neues Sample eintragen
                    $.ajax({
                        url: SLMANIA_URL + 'backend/newsample.php',
                        type: 'POST',
                        data: 'id=' + escape(user_id) + '&text=' + escape($('table.areadescription td.areadescription').text().trim()),
                        dataType: 'json',
                        complete: function (data) {
                            console.log(data.responseText);
                            data = JSON.parse(data.responseText);
                        }
                    });//*/

                    // play alarm
                    if (alarm === true) {
                        $('<audio/>', {
                            id: 'slmania-alarm-sound',
                            loop: false,
                            controls: false,
                            autoplay: true,
                            src: chrome.extension.getURL('../sounds/bloodalarm.ogg')
                        }).appendTo($('body'));

                        alarm_observer(true);
                    }

                    // Zeitpunkt merken
                    localStorage.setItem('slmania-lastsample', Math.floor(Date.now() / 1000));
                } else {
                    marked_npc.del('bloodsamples');
                }
            }//*/

            if (!wanted_npc && alarm === true) {
                alarm_observer();
            }
        } else if (house !== null) {
            (function () {
                var key = place.x + '|' + place.y,
                    places = JSON.parse(localStorage.getItem('slmania-places')) || {},
                    desc = '';
                
                $('table.areadescription td.areadescription').contents().each(function () {
                    if (this.nodeType == 3) {
                        desc += $(this).text();
                    } else if ($(this).hasClass('editcaption')) { // break vor den uk gegenständen
                        return false;
                    }
                });
                
                places[key] = {
                  area_id: house,
                  desc: $.trim(desc),
                  gfx: $('#mainmapx' + place.x + 'y' + place.y).attr('src'),
                  name: $('table.areadescription td.mainheader').text().trim()
                };
                
                localStorage.setItem('slmania-places', JSON.stringify(places));
            })();
        }
    }

    last_sample_id = window.setInterval(function () {
        var diff = Math.floor(Date.now() / 1000) - +localStorage.getItem('slmania-lastsample'),
            hint_msg = $('#slmania-hint').text(),
            parts = [];

        // Hinweis für einen Zyklus stehen lassen
        if (hint_msg === '' || ('' + $('#slmania-hint').attr('class')).indexOf('slmania-timeout') !== -1) {
            if (diff < 60 * 120) {
                if (diff >= 60) {
                    parts.push(Math.floor(diff / 60) + 'm');
                }
                if (diff % 60) {
                    parts.push((diff % 60) + 's');
                }

                hint_msg = parts.join(', ');
            } else {
                //hint_msg = 'Sample-Timeout';
                window.clearInterval(last_sample_id);
            }

            $('#slmania-hint').text(hint_msg);
        }
        $('#slmania-hint').addClass('slmania-timeout');

    }, 1000);

    // Log Interface erstellen
    $('body').prepend($('<div/>', {
        id: 'slmania-overlay'
    }));
    create_interface().appendTo($('body'));

    $('#slmania-content').append($('<a/>', {
        id: 'slmania-submit',
        title: 'Eintraege abschicken',
        href: '#',
        style: 'background-image: url(' + chrome.extension.getURL('images/save_labled_go.png') + ') !important;',
        click: function () {
            var passages = data.get('passages', []),
                places = data.get('places', {});
            
            $.ajax({
                url: SLMANIA_URL + 'backend/entry.php',
                type: 'POST',
                data: 'id=' + escape(user_id) + "&log=" + escape(JSON.stringify(log)) + 
                      "&places=" + escape(JSON.stringify(places)) +
                      "&passages=" + escape(JSON.stringify(passages)),
                dataType: 'json',
                complete: function (xhr) {
                    var msg = "", json;
                    console.log(xhr.responseText);
                    json = JSON.parse(xhr.responseText);
                    console.log(json);

                    if (typeof json.msg === 'string') {
                        msg = json.msg;
                    } else {
                        log = [];
                        localStorage.setItem('slmania-log', JSON.stringify(log));
                        data.save('places', null);
                        data.save('passages', null);
                        
                        msg = json.inserted + " neu, " + json.updated + " updated, " + 
                              json.places_changed + " places";
                    }

                    $('#slmania-hint').text(msg);
                    $('#slmania-hint').removeClass('slmania-timeout');
                }
            }).fail(function () {
                $('#slmania-hint').text('something went terribly wrong');
                $('#slmania-hint').removeClass('slmania-timeout');
                console.log(this);
            });
        }
    }), $('<a/>', {
        id: 'slmania-show',
        title: 'Eintraege auflisten',
        href: '#',
        style: 'background-image: url(' + chrome.extension.getURL('images/window_text.png') + ') !important;',
        click: function () {
            var table = null,
                i,
                length,
                row;

            //console.log(log);

            localStorage.setItem('slmania-toogle-click', this.id);

            $('#slmania-overlay-content *:not(#slmania-overlay-close)').remove();
            table = $('<table/>', {
                id: 'slmania-logs',
                html: '<tr><th>NPC-Name</th><th>Drops</th><th>Position</th><th></th></tr>'
            });

            for (i = 0, length = log.length; i < length; i += 1) {
                if (typeof log[i] === 'string') {
                    row = JSON.parse(log[i]);
                } else if (log[i] === null) {
                    continue;
                } else if (log[i].item) {
                    row = {
                        id: log[i].id,
                        npc: {
                            name: log[i].item,
                            place: log[i].place
                        },
                        drops: []
                    }
                } else {
                    row = log[i];
                }

                $('<tr/>', {
                    html: "<td>" + row.npc.name + " (" + row.id + ", " + row.action + ")</td><td>" +
                          row.drops.join(', ') + "</td><td>" + (row.npc.place ? row.npc.place.x + ' ' + row.npc.place.y : 'unbekannt') +
                          "</td><td><a href=\"javascript: ;\" id=\"slmania-entry-" + i + "\">X</a></td>"
                }).appendTo(table);
            }

            table.prependTo($('#slmania-overlay-content'));
            $('#slmania-overlay').show();

            $('#slmania-overlay-content table a').click(function () {
                var i = +this.id.replace(/slmania-entry-/, '');

                $(this).parents('tr').remove();

                delete log[i];
                localStorage.setItem('slmania-log', JSON.stringify(log));
            });

            $('<a/>', {
                click: function () {
                    var places = JSON.parse(localStorage.getItem('slmania-places')) || {},
                        table = $('<table id="slmania-places"><tr><th>Posi</th>'+
                                 '<th>gfx</th><th>Name</th><th>desc</th></tr></table>');

                    $.each(places, function (key, place) {
                        var pos = key.split('|');

                        $('<tr><td>X: ' + pos[0] + ' Y: ' + pos[1] + '</td>'+
                          '<td><img width=20 height=20 src="' + place.gfx + '">'+
                          '</td><td>' + place.name + '</td>'+
                          '<td>' + place.desc.slice(0,80) + '</td></tr>').appendTo(table);
                    });

                    $('#slmania-overlay-content table').replaceWith(table);
                },
                href: 'javascript: ;',
                text: 'Felder anzeigen'
            }).prependTo($('#slmania-overlay-content'));
            
            $('<a/>', {
                click: function () {
                    var passages = data.get('passages', []),
                        table = $('<table id="slmania-passages"><tr><th>Von</th>'+
                                 '<th>Nach</th><th>Via</th></tr></table>');
                    
                    console.log(passages);
                    
                    $.each(passages, function (i, passage) {
                        var pos_string = function (place) {
                                if (place) {
                                    return 'X: ' + place.x + ' Y: ' + place.y;
                                }
                                
                                return place;
                            };
                        $('<tr><td>' + pos_string(passage.from) + '</td>'+
                          '<td>' + pos_string(passage.to) + '</td>'+
                          '<td>' + passage.via + '</td></tr>').appendTo(table);
                    });

                    $('#slmania-overlay-content table').replaceWith(table);
                },
                href: 'javascript: ;',
                text: 'Passagen anzeigen'
            }).prependTo($('#slmania-overlay-content'));
        }
    }), $('<input/>', {
        id: 'slmania-active',
        type: 'checkbox',
        title: 'Slmania an-/ausschalten',
        checked: localStorage.getItem('slmania-active') ? true : false,
        click: function () {
            //console.log(this.checked);
            localStorage.setItem('slmania-active', this.checked ? 1 : '');
        }
    }), $('<a/>', {
        id: 'slmania-bloodsample-show',
        title: 'gesuchte Blutprobenmöglichkeiten anzeigen',
        href: '#',
        style: 'background-image: url(' + chrome.extension.getURL('images/blutechse.gif') + ') !important;',
        click: function () {
            window.setTimeout(function () { // delayed display for clearing #slmania-name_xy
                var marked = marked_npc.get(),
                    name = window.location.href.split('#slmania-name_'),
                    list = $('<ul/>', {
                        id: 'slmania-bloodsample-list'
                    }),
                    i,
                    length;
                //console.log(marked);

                if (name.length > 1 && (name = name[1])) {
                    marked = marked[name];
                    list.html('<li>gesucht: ' + name + '</li>');
                } else {
                    marked = marked['bloodsamples'] || {};
                    list.html('<li>gesucht: ' + (marked.name || 'nichts') + ' ' +
                              (!marked ? '' : marked.msg || '') + '</li>');
                }

                if (marked) {
                    for (i = 0, length = (marked.npcs || []).length; i < length; i += 1) {
                        $('<li/>', {
                            html: marked.npcs[i].count + 'x ' +
                                  marked.npcs[i].name +
                                  ' (Position X: ' + marked.npcs[i].x +
                                  ' Y: ' + marked.npcs[i].y + ')'
                        }).appendTo(list);
                    }
                }

                $('#slmania-overlay-content *:not(#slmania-overlay-close)').remove();
                list.prependTo($('#slmania-overlay-content'));
                $('#slmania-overlay').show();
            }, 1);
            
            localStorage.setItem('slmania-toogle-click', this.id);
        }
    }), $('<input/>', {
        id: 'slmania-bloodsample-alarm',
        type: 'checkbox',
        title: 'Blutprobenalarm an/aus',
        checked: alarm,
        click: function () {
            alarm = alarm ? false : true;
            localStorage.setItem('slmania-alarm', JSON.stringify(alarm));

            alarm_observer(!alarm);
        }
    }), $('<a/>', {
        id: 'slmania-mark_npc',
        title: 'NPC auf Karte markieren',
        href: '#',
        style: 'background-image: url(' + chrome.extension.getURL('images/window_gallery.png') + ') !important;',
        click: function () {
            var list = $('<ul/>', {
                    id: 'slmania-marked_npc-list'
                }),
                marked = marked_npc.get(),
                name = '';
            //console.log(marked_npc);

            if (marked) {
                for (name in marked) {
                    $('<li/>', {
                        html: name,
                        id: 'slmania-mark_npc_name_' + name
                    }).append($('<span/>', {
                        'class': 'slmania-marker_color',
                        style: 'background-color: ' + marked[name].color + ';'
                    }), $('<a/>', {
                        href: '#slmania-name_' + name,
                        html: 'Show',
                        click: function () {
                            window.setTimeout(function () {
                                $('#slmania-bloodsample-show').click();
                            }, 1);
                        }
                    }), $('<a/>', {
                        href: '#',
                        html: 'Reload',
                        'class': 'slmania-mark_npc_reload',
                        click: function () {
                            var group = $(this).parent()[0].id.replace(/slmania\-mark_npc_name_/, ''),
                                old = marked_npc.get()[group],
                                extend = {};

                            if (old.name) {
                                extend.name = old.name;
                            }
                                
                            // refresh coords
                            marked_npc.add(group, old.color, null, extend);
                        }
                    }), $('<a/>', {
                        href: '#',
                        html: 'X',
                        'class': 'delete',
                        click: function () {
                            var name = $(this).parent()[0].id.replace(/slmania\-mark_npc_name_/, '');
                            
                            if (marked_npc.del(name) === true) { // success
                                console.log(name + ' gelöscht');
                                
                                // reload
                                $('#slmania-mark_npc').click();
                            } else {
                                console.log(name + ' nicht gefunden');
                            }
                        }
                    })).appendTo(list);
                }
            }

            $('#slmania-overlay-content *:not(#slmania-overlay-close)').remove();
            list.prependTo($('#slmania-overlay-content'));
            $('#slmania-overlay-content').prepend($('<label/>', {
                'for': 'slmania-new_marker_name',
                html: 'Name'
            }), $('<input/>', {
                id: 'slmania-new_marker_name'
            }), $('<label/>', {
                'for': 'slmania-new_marker_color',
                html: 'Farbe'
            }), $('<input/>', {
                id: 'slmania-new_marker_color'
            }), $('<input/>', {
                type: 'button',
                value: 'Eintragen',
                click: function () {
                    var color = $('#slmania-new_marker_color').val().trim(),
                        name = $('#slmania-new_marker_name').val().trim();
                    
                    console.log(color, name)
                    if (!name || !color) {
                        console.log($('#slmania-new_marker_color'), $('#slmania-new_marker_name'));
                        return true;
                    }
                    
                    // npc auf karte markieren
                    marked_npc.add(name, color, function () {
                        // refresh
                        $('#slmania-mark_npc').click();
                    });
                    
                    return true;
                }
            }), $('<a/>', {
                html: 'reload_all',
                href: '#',
                click: function () {
                    $('.slmania-mark_npc_reload').click();
                }
            }));
            $('#slmania-overlay').show();

            localStorage.setItem('slmania-toogle-click', this.id);
        }
    }), $('<a/>', {
        id: 'slmania-clear',
        title: 'Eintraege leeren',
        href: '#',
        style: 'background-image: url(' + chrome.extension.getURL('images/sign_cacel.png') + ') !important;',
        click: function () {
            log = [];
            localStorage.setItem('slmania-log', JSON.stringify(log));
        }
    }), $('<a/>', {
        id: 'slmania-throw',
        title: 'Brunnen',
        href: '#',
        style: 'background-image: url(../images/icon_dropgold.gif) !important;',
        click: function () {
            var runs = 0;
                
            window.setInterval(function () {
                $.ajax('http://welt3.freewar.de/freewar/internal/main.php?werfe=1', {
                    cache: false,
                    complete: function () {
                        runs++;

                        if (runs % 50 == 0) {
                            console.log(runs);
                            $('#slmania-hint').text(runs);
                        }
                    }
                 });
            }, 5);
            
            $('#block_reload').val('true');
        }
    }), $('<a/>', {
        id: 'slmania-quickjump',
        title: 'Quickjump',
        href: '#',
        style: 'background-image: url(../images/items/zauberkugel.gif) !important;',
        click: function () {
            var list = $('<ul id="slania-teleporters"></ul>'),
                teleporter =  {
                    'zauberkugel':    ['gepresste Zauberkugel', 'Schattenflügel', 'Nebel der Teleportation'],
                    'glzauberkugel':  ['geklebte Zauberkugel', 'gelbe Zauberkugel'],
                    'portalmaschine': ['rote Portalmaschine', 'Portalmaschien']
                };
            
            $('#slmania-overlay-content *:not(#slmania-overlay-close)').remove();
            
            $.each(teleporter, function (name, items) {
                list.append($('<li>').html($('<a/>', {
                    click: function () {
                        $.get(
                            'http://welt3.freewar.de/freewar/internal/item.php', 
                            {'action': 'openinv', 'selcat': 5}, 
                            function (data) {
                                $('<div/>').html(data).find('.listitemrow').each(function (i, itemrow) {
                                    console.log(itemrow);
                                })
                            }
                        );
                    },
                    href: '#',
                    html: name,
                    id: 'slmania-teleporter-' + name
                })));
            });

            $('#slmania-overlay-content').append(
                list,
                $('<div id="slmania-teleporter-items"/>')
            );

            $('#slmania-overlay').show();

            localStorage.setItem('slmania-toogle-click', this.id);
        }
    }), $('<a/>', {
        id: 'slmania-combinations',
        href: '#',
        html: '12',
        title: 'Kombinationen',
        click: function () {
            var combinations = JSON.parse(localStorage.getItem('slmania-combinations')) || {},
                list = $('<ul/>');
            
            $('#slmania-overlay-content *:not(#slmania-overlay-close)').remove();
            
            $.each(combinations, function (id, combination) {
               list.append($('<li>' + id + ': ' + combination.join('') + '</li>')) 
            });
            
            $('#slmania-overlay-content').append(list);
            
            $('#slmania-overlay').show();

            localStorage.setItem('slmania-toogle-click', this.id);
        }
    }), $('<span/>', {
        id: 'slmania-hint',
        html: hint_msg
    }));

    // Log Overlay
    $('#slmania-overlay').append($('<div/>', {
        id: 'slmania-overlay-transparent-layer'
    }), $('<div/>', {
        id: 'slmania-overlay-content'
    }));

    $('<a/>', {
        id: 'slmania-overlay-close',
        href: '#',
        html: 'X',
        click: function () {
            $('#slmania-overlay').hide();
            localStorage.setItem('slmania-toogle-click', '');
        }
    }).appendTo($('#slmania-overlay-content'));

    if (localStorage.getItem('slmania-toogle-click')) {
        $('#' + localStorage.getItem('slmania-toogle-click')).click();
    }
    
    //*
    $('select[name="setoption"] option:selected').each(function (i, option) {
        var name = '',
            pics = [],
            text = '';
        
        option = $(option);
        
        if (option.val()) {
            name = option.text().trim();
            
            $('.uimgborder img').each(function (i, img) {
                pics.push('(' + name + ') ' + $(img).attr('src').replace(/^\.\.\/images\/map\//, ''));
            });
            
            text = pics.join("\n") + "\n";
                
            $('<textarea/>', {
                rows: pics.length + 10,
                cols: 100,
                html: text
            }).appendTo($('body'));
        }
    });//*/

    // display pageaction
    //chrome.extension.sendRequest({}, function () {});
}(window));
