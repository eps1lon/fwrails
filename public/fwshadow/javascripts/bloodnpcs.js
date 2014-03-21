(function (window, $, undefined) {
    'use strict';
    var document = window.document,
        datagroups = {
            /*
            'live': {
                push: function (data, npc, dummy) {
                    return (data.push([dummy.live, npc.live]) - 1);
                }
            },//*/
            'maxdmg': {
                push: function (data, npc, dummy) {
                    return (data.push([dummy.live, npc.maxdmg]) - 1);
                }
            },//*/
            'maxdmg / dummy.live': {
                push: function (data, npc, dummy) {
                    return (data.push([dummy.live, +(npc.maxdmg / dummy.live).toFixed(3)]) - 1);
                }
            },
            'live / dummy.live': {
                push: function (data, npc, dummy) {
                    return (data.push([dummy.live, +(npc.live / dummy.live).toFixed(3)]) - 1);
                }
            }//*/
            /*
            'npc.live(npc.maxdmg)': {
                push: function (data, npc, dummy) {
                    return push(data, parseFloat((npc.live / npc.maxdmg).toFixed(5)), 0);
                }
            },//*/
            /*
            'live % maxdmg': {
                push: function (data, npc, dummy) {
                    return (data.push([npc.live, npc.maxdmg]) - 1);
                }
            }//*/
        };
    
    $.each(datagroups, function (id, group) {
        group.data = [];
        group.container = $('<fieldset/>', {
            id: 'datagroup_' + id,
            html: '<legend>' + id + '</legend><table></table>'
        });
        group.max = undefined;
        group.min = undefined;
        group.avg = 0;
        group.sum = 0;
        
        group.plot = $('<div/>');
    });
    
    $(document).ready(function () {
        $.when($.getJSON('http://localhost/js/grease/backend/npcs.php'),
               $.getJSON('http://localhost/js/grease/slmania/data/bloodnpcs.json')).
            then(function (a, b) {
                var npcs = a[0].npcs,
                    bloodnpcs = b[0];
               
               //console.log(npcs, bloodnpcs);
               
                $.each(bloodnpcs, function (prefix, data) {
                    console.log(prefix, data);
                    $.each(data, function (i, npc) {
                        $.each(datagroups, function (id, group) {
                            var i = 0;
                            
                            if (
                                    typeof npc === 'object' &&
                                    npc.name != 'Sandgolem' && 
                                    npc.name.indexOf('Undaron') === -1 &&
                                    npc.name.indexOf('Graustein') === -1 &&
                                    npcs[npc.name] && typeof npc.live != 'string' && 
                                    typeof npc.maxdmg != 'string' && npc.live > 0 && 
                                    npc.maxdmg > 0 && npcs[npc.name].live > 0
                               ) {
                                
                                i = group.push(group.data, npc, npcs[npc.name]);
                                
                                //console.log(group.data[i], npc);

                                if (group.data.length === 1) {
                                    group.max = [group.data[0], npc.name];
                                    group.min = [group.data[0], npc.name];
                                    
                                    group.avg = group.data[0][1];
                                } else if (group.data[i][1] > group.max[0][1]) {
                                    group.max = [group.data[i], npc.name];
                                } else if (group.data[i][1] < group.min[0][1]) {
                                    group.min = [group.data[i], npc.name];
                                } 
                                
                                group.sum += group.data[i][1];
                            }
                       });
                    });
                });
                
                $.each(datagroups, function (id, group) {
                    group.avg = group.sum / group.data.length;
                    console.log(id,
                                '\nMin: ', group.min,
                                '\nMax: ', group.max,
                                '\nSum: ', group.sum,
                                '\nAvg: ', group.avg);
                                
                    $.each('min max sum avg'.split(' '), function (i, value) {
                        $('table', group.container).append($('<tr/>', {
                            className: value,
                            html: '<td>' + value + '</td><td>' + group[value] + '</td>'
                        }));
                    });
                    
                    group.plot.appendTo(group.container);
                    group.container.appendTo($('body'));
                    
                    //group.plot.width(group.plot.height());
                    $.plot(group.plot, [group.data], {
                        points: {show: true}
                    });
                });
                
                
            });
    });
    
}(window, jQuery));