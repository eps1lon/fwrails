    // Schlüssel des Users (nicht mit Name oder ID verwechseln)
var AUTHENTICITY_TOKEN = '721366d55104bb1765173fe40895a29cf76c7dae',
    // Adresse zum Ordner des Tools mit Backend und Frontend
    SLMANIA_URL = 'http://fwrails.net/slmania/';

/*jslint devel: true, browser: true, continue: true, nomen: true, regexp: true, maxerr: 50, indent: 4 */
(function (window, __undefined) {
    "use strict";
    var $ = window.jQuery,
        attacked_npc,
        container = null,
        data = window.Data,
        drops = [],
        hint_msg = '',
        old_items = data.get('items', {}),
        items = {},
        item_id = 0,
        log = data.get('log', []),
        log_msg = '',
        matches = [],
        old_npcs = data.get('npcs', {}),
        npcs = {},
        params = $SERVER.params,
        passages = data.get('passages', []),
        place = null,
        unique_npcs = {
            // in db unique_npc + 1
            "": 0,
            "Unique-": 1,
            "Gruppen-": 2,
            "Interaktions-": 4, // taucht so nicht im Spiel auf. wird nur in db verwendet
            "Resistenz-": 5,
            "Superresistenz-": 6
        },
        unique_npc_pattern = new RegExp('((' + $.map(unique_npcs, function (_, key) { return key; }).join('|') + ')NPC)'),
        house = data.get('house', null);
        
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
        
        if (params.do === 'go') {
            passages.push({'from': place, 'to': null, 'via': params});
            data.save('passages', passages);
        }
    } else {
        house = null;
    }
    
    data.save('house', house);
    
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
                
                // probleme mit sonderzeichen in "stärke". kein konsistentes pattern gefunden
                matches = content.text().match(/(\d+)\.\s*$/);
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

                //matches = content.text().match(/\(((Gruppen-|Unique-|)NPC)\)/); //matches[1]
                matches = unique_npc_pattern.exec(content.text()); //matches[2]
                
                if (matches !== null) {
                    unique = unique_npcs[matches[2]];
                } else {
                    unique = -1;
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
    } else if (params.action === 'take') { // Item genommen (Pflanze geerntet)
        var item = old_items[params.act_item_id];
        
        if (item) {
            log_msg = {
                id: +params.act_item_id,
                item: item,
                place: place,
                action: 'take'
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
    //*/
      
      // Ort Verarbeitung 
    if (place !== null) {
        if (house !== null) {
            (function () {
                var key = place.x + '|' + place.y,
                    places = data.get('places', {}),
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
                
                data.save('places', places);
            })();
        }
    }
    
    // items speichern
    data.save('items', items);
    
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
                data: 'id=' + escape(AUTHENTICITY_TOKEN) + "&log=" + escape(JSON.stringify(log)) + 
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
                    };
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
            
            $('#slmania-overlay-content').prepend($('<a/>', {
                href: SLMANIA_URL + 'frontend/evaluation.php',
                target: '_blank',
                text: 'Zur Auswertung'
            }));
            
            $('#slmania-overlay').show();

            $('#slmania-overlay-content table a').click(function () {
                var i = +this.id.replace(/slmania-entry-/, '');

                $(this).parents('tr').remove();

                delete log[i];
                localStorage.setItem('slmania-log', JSON.stringify(log));
            });
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
        id: 'slmania-clear',
        title: 'Eintraege leeren',
        href: '#',
        style: 'background-image: url(' + chrome.extension.getURL('images/sign_cacel.png') + ') !important;',
        click: function () {
            log = [];
            localStorage.setItem('slmania-log', JSON.stringify(log));
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
}(window));