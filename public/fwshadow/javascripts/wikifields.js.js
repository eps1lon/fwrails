(function (window, undef) {
    "use strict";
    var document = window.document,
        clear = function () {
            document.getElementById('console').innerHTML = "";
            log("...");
        },
        log = function (text, newline) {
            console.log(text);
            document.getElementById('console').innerHTML += text + (newline ? "<br>" : "");
        },
        places = {},
        trim = function (str) {
            if (typeof str === 'string') {
                str = str.replace(/^\s*([\S\s]*?)\s*$/, '$1');
            }
            return str;
        },
        basename = function (path) {
            return path.match(/([^/]*)$/)[1];
        },
        wiki_categories = function (text) {
            var container = null,
                html = document.createElement('div'),
                i = 0,
                length = 0,
                xhttp = null, 
                area = '',
                articles = [],
                places = {},
                url = '';
            
            log("creating dom-tree (category)...");
            html.innerHTML = text;
            container = html.getElementsByTagName('table')[0].getElementsByTagName('li');
            log("done", true);
            
            log("traversing articles...");
            for (i = 0, length = container.length; i < length; i += 1) {
                articles.push(container[i].getElementsByTagName('a')[0]);
            }
            log("found " + articles.length, true);
            
            log("fetching article list...", true);
            for (i = 0, length = articles.length; i < length; i += 1) {
                area = articles[i].innerHTML.replace(/Felder:\s*/, '');
                log("Area: " + area, true);
                
                url = "http://localhost/js/grease/backend/wikiproxy.php?type=field&site=" + escape(trim(area).replace(/\s+/g, "_"));
                xhttp = new XMLHttpRequest();
                xhttp.open("GET", url, false);
                xhttp.send(null);
                if (xhttp.status === 200) {
                    places[area] = wiki_fields(xhttp.responseText);
                } else {
                    log("an error (#" + xhttp.status + ") occured while requesting " + articles[i], true);
                    break;
                }
            }
            log("done", true);
            
            return places;
        },
        wiki_fields = function (text) {
            var container = null,
                fields = [],
                html = document.createElement('div'),
                i = 0,
                length = 0,
                matches = null;
                
            log("creating dom-tree (article)...");
            html.innerHTML = text;
            log("done", true);
            
            log("parsing article...");
            
            //*
            container = html.getElementsByTagName('textarea')[0];
            matches = container.innerHTML.split("{{Vorlage:Feldzusammenfassung/Layout");
            
            for (i = 1, length = matches.length; i < length; i += 1) {
                matches[i] = matches[i].replace(/\n\}\}.*/, '')
                fields.push(wiki_field_each(matches[i]));
            }//*/
            
            
            /* append html to use getElementById
            document.getElementById('mw-content').appendChild(html);

            container = document.getElementById('bodyContent');
            document.getElementById('mw-content').removeChild(html);
            
            container = container.getElementsByTagName('table');
            for (i = 1, length = container.length; i < length; i += 1) { // first Table ist minimap
                fields.push(wiki_field_each(container[i]));
            }//*/
            
            log("found " + fields.length + " fields", true);
            
            return fields;
        },
        wiki_field_each = function (container) {
            var field = {
                    name: '',
                    pos_x: -10,
                    pos_y: -9,
                    article: null
                },
                i = 0,
                length = 0,
                matches = null,
                params = container.split("|");
            
            for (i = 0, length = params.length; i < length; i += 1) {
                matches = params[i].split("=", 2);
                
                matches[1] = trim(matches[1]);
                switch (matches[0]) {
                    case "Name":
                        field.name = matches[1];
                        break;
                    case "X":
                        field.pos_x = +matches[1];
                        break;
                    case "Y":
                        field.pos_y = +matches[1];
                        break;
                    case "Ort":
                        field.article = matches[1];
                        break;
                    case "Bild":
                        field.gfx = basename(matches[1]);
                        break;
                }
            }

            return field;
        };
        
       
    window.addEventListener('load', function () {
        clear();
        
        document.getElementById('start').addEventListener('click', function () {
            var xhttp = new XMLHttpRequest();
            
            log("retrieving category-list...", true);
            
            // http://www.fwwiki.de/index.php/Kategorie:Felder
            // http://localhost/js/grease/slmania/Kategorie%20Felder%20%E2%80%93%20FreewarWiki.html
            xhttp.open("GET", "http://localhost/js/grease/backend/wikiproxy.php?type=category", true);
            xhttp.onreadystatechange = function () {
                var add = '',
                    area = '',
                    date = '',
                    i = 0,
                    j = 0,
                    length = 0,
                    text = '';
                if (this.readyState === 4) {
                    places = wiki_categories(this.responseText);
                    /*
                    log("processing places:", true);
                    
                    log("placelist...");
                    text = "";
                    text += "<!-- ACHTUNG: Diese Seite wird von einem Bot aktualisiert. Wenn Du Veränderungen am Aufbau dieser Seite vornimmst, hinterlasse bitte eine Nachricht auf der Diskussionsseite, sonst werden die Änderungen vom Bot überschrieben. -->Diese Seite listet, nach [[Gebiet]]en geordnet, alle im Wiki eingetragenen [[Ort]]e mit deren Koordinatenangaben.\n";
                    for (area in places) {                        
                        add = "";
                        for (i = 0, j = 0, length = places[area].length; i < length; i += 1) {
                            if (places[area][i].article && places[area][i].article !== "none") {
                                if (j > 0) {
                                    add += ";";
                                }
                                
                                add += "[[" + places[area][i].article + "]]: ";
                                add += places[area][i].pos_x + "," + places[area][i].pos_y;
                                
                                j += 1;
                            }
                        }
                        
                        if (j > 0) {
                            text += "<!--\n-->{{Überschriftensimulation 2|1={{Gebietslink|" + area + "}}}}";
                            text += add;
                        }
                    }
                    document.getElementById('placelist').innerHTML = text;
                    log("done", true);
                    
                    log("coordlist...");
                    text = "";
                    text += "Einige Zauber und Funktionen in Freewar verraten die aktuelle Position eines Charakters in Form von Koordinaten. Gerade bei Feldern, die nicht zur oberirdischen Hauptlandmasse von Freewar gehören, ist es oft schwer, herauszufinden, zu welchem Gebiet diese Koordinaten gehören.<br />Die folgende Liste hilft dabei. Alle Koordinaten sind in der Form '''X''','''Y''' unter dem Namen des Gebiets gelistet, zu dem sie gehören. So kann mit der Suchfunktion des Browsers leicht das Gebiet zu einer bestimmten Position ermittelt werden.<br />Die Liste ist automatisch aus den Wiki-Kartendaten erstellt (Stand " + date + ") und wird evtl. bei Kartenänderungen oder Fehlern auch automatisch wieder neu generiert; Änderungen an der Liste sind nicht sinnvoll. Stattdessen, wenn etwas auffällt, bitte auf der Diskussionsseite vermerken.<br />"
                    for (area in places) {
                        text += "<!--\n-->{{Überschriftensimulation 2|1={{Gebietslink|" + area + "}}}}";
                        
                        for (i = 0, length = places[area].length; i < length; i += 1) {
                            if (i > 0) {
                                text += ";";
                            }
                            text += places[area][i].pos_x + "," + places[area][i].pos_y;
                        }
                    }
                    document.getElementById('coordlist').innerHTML = text;
                    log("done", true);
                    */
                    log("json...");
                    document.getElementById('fields_json').innerHTML = JSON.stringify(places);
                    log("done", true);
                }
            };
            xhttp.send(null);
            
        }, false);
        
        //document.getElementById('start').click();
    }, false);   
}(this));