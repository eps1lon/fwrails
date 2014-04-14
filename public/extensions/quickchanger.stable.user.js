// ==UserScript==
// @name           Itemwechsel
// @description    Schnelles wechseln von Items
// @include        http://*.freewar.de/freewar/internal/item.php*
// @version        1.0
// @grant          none
// ==/UserScript==
'use strict';
var styles = {
        'freewar3-quickchange-caption': '',
        'freewar3-quickchange-close': '',
        'freewar3-quickchange-container': '',
        'freewar3-quickchange-li': '',
        'freewar3-quickchanger': ''
    },
    document = window.document,
    addEvent = function (obj, type, fn) {
        var r = true;
        if (obj.addEventListener) {
            obj.addEventListener(type, fn, false);
        } else if (obj.attachEvent) {
            obj['e' + type + fn] = fn;
            obj[type + fn] = function () {
                obj['e' + type + fn](window.event);
            };
            r = obj.attachEvent('on' + type, obj[type + fn]);
        } else {
            obj['on' + type] = fn;
        }
        return r;
    },
    create_linklist = function (items) {
        var close = document.createElement('a'),
            container = document.createElement('div'),
            list = document.createElement('span'),
            list_element = '',
            i = 0;

        container.className = 'freewar3-quickchange-container';
        container.setAttribute('style', styles['freewar3-quickchange-container']);
        container.innerHTML = '<p style="' + styles['freewar3-quickchange-caption'] + '" ' +
                                 'class="freewar3-quickchange-caption">' +
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
            list_element = '<p class="freewar3-quickchange-li" ' +
                           'style="' + styles['freewar3-quickchange-li'] + '">' +
                           '<b class="' + items[i].className + '">' + items[i].name + '</b>' + 
                           '<a href="' + items[i].href + '">Aktivieren</a></p>';
            list.innerHTML += list_element;
        }
    },
    get_list = function (event) {
        var path = this.href,
            xhr = new window.XMLHttpRequest();

        xhr.open('GET', path, true);
        // set RequestHeader is ignored in FF, Chrome automatically detects
        // the correct charset. FF would give %uFFFD chars
        xhr.overrideMimeType('text/html; charset=iso-8859-1');
        xhr.onreadystatechange = function () {
            var dom_helper = null,
                i = 0,
                itemrows = [],
                items = [],
                name = '';

            if (xhr.readyState === 4) {
                // dom temporär erstellen
                dom_helper = document.createElement('div');
                dom_helper.innerHTML = xhr.responseText;

                itemrows = dom_helper.getElementsByClassName('listitemrow');

                // skip first, only desc
                for (i = 1; i < itemrows.length; i += 1) {
                    name = itemrows[i].firstChild.innerHTML;

                    items.push({
                        'name': name,
                        'href': itemrows[i].getElementsByTagName('a')[0].href,
                        'className': itemrows[i].firstChild.className
                    });
                }

                // linkliste erstellen
                create_linklist(items);
            }
        };
        xhr.send(null);
        
        event.preventDefault();
        return false;
    },
    append_listlink = function (selector, filter, text) {
        var container = null,
            link;

        // search container
        container = document.getElementById(selector);
        if (container === null) {
            link = document.createElement('a');
            link.href = 'item.php?action=' + filter + 'select';
            link.innerHTML = text;
           
            document.getElementById('listrow_status').appendChild(link);
        } else {
            link = container.getElementsByTagName('a')[0];
        }
            
            
        link.className = 'freewar3-quickchanger';
        link.setAttribute('style', styles['freewar3-quickchanger']);
        link.setAttribute('title', 'Schnellwechsel ' + text);
        addEvent(link, 'click', get_list);
            
        return container;
    };

// angriffswaffe
append_listlink('listrow_attackw', 'a', 'Angriffswaffe');
// verteidigungswaffe
append_listlink('listrow_defensew', 'd', 'Verteidigungswaffe');
// hals
append_listlink('listrow_neck', 'h', 'Halsschmuck');
