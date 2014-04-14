// ==UserScript==
// @name           Laufzeit in Tab
// @description    Zeigt Welt und Laufzeit im Tab-Titel
// @include        http://*.freewar.de/freewar/internal/friset.php*
// @version        1.0
// @grant          none
// ==/UserScript==


var tab = window,
    document = tab.document,
    map_frame = document.getElementsByName('mapFrame')[0],
    subdomain = tab.location.host.split('.')[0],
    timeout_id = -1,
    title = '',
    world_match = subdomain.match(/(\d+)$/),
    set_title = function (window, title, runtime) {
        window.document.title = title;
        if (runtime) {
            window.document.title += ':' + runtime;
        }
    },
    timeout_runtime = function () {
        var document = map_frame.contentWindow.document,
            i = 0,
            runtime = document.getElementById('test'),
            runtime_matches = null
            runtime_parts = [],
            runtime_text = [];
        
        if (runtime) {
            runtime_matches = runtime.innerHTML.match(/(\d+) (\w+)/g);
            
            // parseable runtime
            if (runtime_matches !== null) {
                // match_all workaround
                for (i = 0; i < runtime_matches.length; i += 1) {
                    runtime_parts = runtime_matches[i].split(' ');
                    runtime_text.push(runtime_parts[0] + 
                                      runtime_parts[1].charAt(0).toLowerCase());
                }
            }
            
            // refresh title
            set_title(tab, title, runtime_text.join(','));
        }
        
        timeout_id = tab.setTimeout(timeout_runtime, 1000);
    };

if (world_match !== null) { // Nummernwelt
    title = 'W' + world_match[0];
} else {
    title = subdomain;
}

set_title(tab, title);

map_frame.onload = function () {
    // setInterval gets punished if you tab out
    timeout_id = tab.setTimeout(timeout_runtime, 1000);
}