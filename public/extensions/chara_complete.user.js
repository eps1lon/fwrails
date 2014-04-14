// ==UserScript==
// @name           CharaComplete
// @description    Zeigt an, zu welchem Zeitpunkt die Fähigkeit ausgelernt ist
// @include        http://*.freewar.de/freewar/internal/ability.php*
// @version        1.0
// @grant          none
// ==/UserScript==
'use strict';
var styles = {
        'freewar3-chara_complete': ''
    },
    document = window.document,
    chara_container = document.getElementsByClassName('listrow')[0],
    cd_std = [0, 0],
    cd_parts = {
        'd': cd_std,
        'h': cd_std,
        'm': cd_std
    },
    chara_complete_node = null,
    now = new Date(),
    then_ms = now.getTime(),
    then = new Date(),
    text_prop = '';

if (chara_container) {
    // cross-browser innerText
    text_prop = chara_container.hasOwnProperty('innerText') ? 'innerText'
                                                            : 'textContent';
    
    cd_parts.d = chara_container[text_prop].match(/(\d+) Tagen/i)   || cd_std;
    cd_parts.h = chara_container[text_prop].match(/(\d+) Stunden/i) || cd_std;
    cd_parts.m = chara_container[text_prop].match(/(\d+) Minuten/i) || cd_std;

    //console.log(now.toLocaleString());
    then_ms += parseInt(cd_parts.d[1], 10) * 24 * 60 * 60 * 1000
            +  parseInt(cd_parts.h[1], 10)      * 60 * 60 * 1000
            +  parseInt(cd_parts.m[1], 10)           * 60 * 1000;

    then.setTime(then_ms);
    //console.log(then.toLocaleString());

    chara_complete_node = document.createElement('p');
    chara_complete_node.id = 'freewar3-chara_complete';
    chara_complete_node.className = 'listrow';
    chara_complete_node.setAttribute('style', styles['freewar3-chara_complete']);
    chara_complete_node.innerHTML = 'Fertig am ' + then.toLocaleString();
    // chara_container.after()
    chara_container.parentNode.insertBefore(chara_complete_node,
                                            chara_container.nextSibling);
}