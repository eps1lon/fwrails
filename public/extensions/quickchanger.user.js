// ==UserScript==
// @name           Itemwechsel
// @description    Schnelles wechseln von Items
// @include        http://*.freewar.de/freewar/internal/item.php*
// @version        1.0
// @grant          none
// ==/UserScript==
'use strict';
var styles = {
        'freewar3-quickchange-caption': 'display: inline-block;',
        'freewar3-quickchange-close': 'float: right;',
        'freewar3-quickchange-container': 'position: fixed; display: inline-block; top: 0px; left: 0px; max-height: 100%; background-color: #ECE9E6; z-index: 9999999; overflow: auto;',
        'freewar3-quickchange-li': 'display: inline-block;',
        'freewar3-quickchanger': ''
    },
    document = window.document,
    create_linklist = function (items) {
        var close = document.createElement('a'),
            container = document.createElement('div'),
            list = document.createElement('span'),
            list_element = '',
            i = 0;

        container.className = 'freewar3-quickchange-container';
        container.setAttribute('style', styles['freewar3-quickchange-container']);
        container.innerHTML = '<p stlye="' + styles['freewar3-quickchange-caption'] + '" ' +
                                 'class="listcaption ' +
                                        'freewar3-quickchange-caption">' +
                                 'Wähle </p>';

        document.body.appendChild(container);

        // create close buttonc
        close.className = 'freewar3-quickchange-close';
        close.setAttribute('style', styles['freewar3-quickchange-close']);
        close.innerHTML = 'X';
        close.href = '#';
        close.onclick = function () {
            // dom4: remove()
            var to_remove = this.parentNode.parentNode;
            to_remove.parentNode.removeChild(to_remove);
        };

        // append Close
        container.firstChild.appendChild(close);

        // append list
        container.appendChild(list);

        // generate list
        for (i = 0; i < items.length; i += 1) {
            list_element = '<p class="listitemrow freewar3-quickchange-li" ' +
                           'stlye="' + styles['freewar3-quickchange-li'] + '">' +
                           '<a href="' + items[i].href + '">' +
                           items[i].name + '</a></p>';
            list.innerHTML += list_element;
        }
    },
    get_inventory_state = function (parent) {
        var filterrow = parent.getElementById('filterrow'),
            selcat = 1,
            selcats = [],
            state = {
                'action': filterrow ? 'openinv' : 'closeinv',
                'selcat': ''
            },
            i = 0;

        if (filterrow) {
            selcats = filterrow.getElementsByTagName('a');
            for (i = 0, selcat = 1; i < selcats.length; i += 1, selcat += 1) {
                // skipped selcat is the current
                if (selcats[i].getAttribute('href') !== 'item.php?selcat=' + selcat) {
                    state.selcat = selcat;
                    break;
                }
            }
        }

        return state;
    },
    get_list = function () {
        var basename = window.location.protocol + '//' +
                       window.location.hostname +
                       window.location.pathname,
            filter = this.getAttribute('data-filter'),
            xhr = new window.XMLHttpRequest(),
            inventory_state = get_inventory_state(document);

        xhr.open('GET', basename + '?action=openinv&' + filter, true);
        // set RequestHeader is ignored in FF, Chrome automatically detects
        // the correct charset. FF would give %uFFFD chars
        xhr.overrideMimeType('text/html; charset=iso-8859-1');
        xhr.onreadystatechange = function () {
            var dom_helper = null,
                equipped = [],
                i = 0,
                itemrows = [],
                items = [],
                name = '',
                xhr_close = null,
                recover_url = '';

            if (xhr.readyState === 4) {
                // dom temporär erstellen
                dom_helper = document.createElement('div');
                dom_helper.innerHTML = xhr.responseText;

                itemrows = dom_helper.getElementsByClassName('listitemrow');

                // skip filterrow
                for (i = 1; i < itemrows.length; i += 1) {
                    equipped = itemrows[i].getElementsByClassName('itemequipped');
                    if (equipped.length > 0) {
                        name = equipped[0].innerHTML;
                    } else {
                        name = itemrows[i].getElementsByTagName('b')[0].innerHTML;
                    }

                    items.push({
                        'name': name,
                        'href': itemrows[i].getElementsByTagName('a')[0].href
                    });
                }

                // linkliste erstellen
                create_linklist(items);

                // recover inventory state
                recover_url = basename + '?action=' + inventory_state.action +
                              '&selcat=' + inventory_state.selcat;
                xhr_close = new window.XMLHttpRequest();
                xhr_close.open('GET', recover_url, true);
                xhr_close.send(null);
            }
        };
        xhr.send(null);
    },
    append_listlink = function (selectors, filter) {
        var container = null,
            i = 0,
            link = document.createElement('a'),
            replace = null;

        // generate link
        link.className = 'freewar3-quickchanger';
        link.setAttribute('style', styles['freewar3-quickchanger']);
        link.setAttribute('data-filter', filter);
        link.setAttribute('title', 'Schnellwechsel ' + selectors[0].split('_')[1]);
        link.innerHTML = '?';
        link.href = '#';
        link.onclick = get_list;

        // search container
        for (i = 0; i < selectors.length; i += 1) {
            container = document.getElementById(selectors[i]);
            if (container !== null) {
                // replace
                replace = container.getElementsByTagName('b')[0];
                if (replace) {
                    link.innerHTML = replace.innerHTML;
                    container.replaceChild(link, replace);
                } else {
                    container.appendChild(link);
                }
                return container;
            }
        }

        return null;
    };

// append_listlink([Selectoren die der Reihe nach versucht werden], 
//                 "selcat aus der die Items genommen werden, frei lassen
//                 für die Aktuelle, selcat=1 für Alle")

// angriffswaffe
append_listlink(['listrow_attackw'], 'selcat=2');
// verteidigungswaffe
append_listlink(['listrow_defensew'], 'selcat=3');
// hals, als fallback in der statusleiste
append_listlink(['listrow_neck', 'listrow_status'], 'selcat=4');
// Zauber in Intelligenz
// append_listlink(['listrow_int'], 'selcat=5');