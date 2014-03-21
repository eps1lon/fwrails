window.onerror = function () {};
(function () {
    "use strict";
    var field = null,
        i,
        length,
        marked_npcs,
        matches = $('p.positiontext').text().match(/Position X: ([0-9\.\-]+) Y: ([0-9\.\-]+)/),
        name,
        npc,
        pos_x, pos_y, 
        wrapped;
    
    if (matches !== null) {
        // parse current pos
        pos_x = +matches[1].replace(/\./, '');
        pos_y = +matches[2].replace(/\./, '');
        
        //console.log("Position X: ", pos_x, " Y: ", pos_y);
        
        marked_npcs = localStorage.getItem('slmania-marked_npc');
        
        marked_npcs = JSON.parse(marked_npcs) || {};
        
        //console.log('marked: ', marked_npcs);

        for (name in marked_npcs) {
            if (marked_npcs[name] && marked_npcs[name].npcs.length > 0) {
                
                for (i = 0, length = marked_npcs[name].npcs.length; i < length; i += 1) {
                    npc = marked_npcs[name].npcs[i];

                    if (pos_x - 2 <= npc.x && pos_x + 2 >= npc.x &&
                        pos_y - 2 <= npc.y && pos_y + 2 >= npc.y) { // map ausschnitt
                        field = $('#mapx' + npc.x + 'y' + npc.y);
                        wrapped = +$('div.slmania-markerwrapper', field).length + 1;

                        $('img', field).attr({
                            width: 50 - wrapped * 2,
                            height: 50 - wrapped * 2
                        }).wrap($('<div/>', {
                            style: 'border: 1px solid ' + marked_npcs[name].color + '; width: ' + (50 - wrapped * 2) + 'px; height: ' + (50 - wrapped * 2) + 'px;',
                            'class': 'slmania-markerwrapper'
                        }));
                    }
                }
            }
        }
    }
    
    //chrome.extension.sendRequest({}, function(response) {});
}());