window.onerror = function () {};
(function () {
    "use strict";
    var chara_container = $('p.listrow:first'),
        cd_std = [0, 0],
        cd_parts = {
            d: cd_std,
            h: cd_std,
            m: cd_std
        },
        now = new Date(),
        then_ms = now.getTime(),
        then = new Date();

    cd_parts.d = chara_container.text().match(/(\d+) Tagen/i)   || [0,0];
    cd_parts.h = chara_container.text().match(/(\d+) Stunden/i) || [0,0];
    cd_parts.m = chara_container.text().match(/(\d+) Minuten/i) || [0,0];
    console.log(cd_parts);
    
    //console.log(now.toLocaleString());
    then_ms += parseInt(cd_parts.d[1], 10) * 24 * 60 * 60 * 1000
            +  parseInt(cd_parts.h[1], 10) *  1 * 60 * 60 * 1000
            +  parseInt(cd_parts.m[1], 10) *  1 *  1 * 60 * 1000
    
    then.setTime(then_ms);                                
    //console.log(then.toLocaleString());
    
    chara_container.after($('<p class="listrow">Fertig am ' + then.toLocaleString() + '</p>'));
}());