(function (window, $, undefined) {
    "use strict";
    var document = window.document;
    
    $(document).ready(function () {
        var grouping = 1,
            e = 0.0,
            id = '',
            datasets = {
                //*
                "Bonus": {
                    filter: function (data) { // typeof null === 'object'
                        return !!(typeof data.bonus === 'object' && data.bonus instanceof Array);
                    }
                },//*,
                "npc-oberirdisch": {
                    filter: function (data) {
                        return !!(!data.special && 
                                !data.dungeon && 
                                !data.aggressive);
                    }
                },
                "npc-dungeon-noaggro": {
                    filter: function (data) {
                        return !!(!data.special && 
                                data.dungeon && 
                                !data.aggressive);
                    }
                },
                "npc-dungeon-aggro": {
                    filter: function (data) {
                        return !!(!data.special && 
                                data.dungeon && 
                                data.aggressive);
                    }
                },
                "unique-oberirdisch": {
                    filter: function (data) {
                        return !!(data.special === 2 && !data.dungeon);
                    }
                },
                "unique-dungeon": {
                    filter: function (data) {
                        return !!(data.special === 2 && data.dungeon);
                    }
                },
                "gruppi-oberirdisch": {
                    filter: function (data) {
                        return !!(data.special === 4 && !data.dungeon);
                    }
                },
                "gruppi-dungeon": {
                    filter: function (data) {
                        return !!(data.special === 4 && data.dungeon);
                    }
                },
                "Blutprobenwesen": {
                    filter: function (data) {
                        return ((data.special&1) ? true : false);
                    }
                }//*/
            },
            create_plot = function (dataset, options) {
                //console.log(dataset, options);
                var container = null,
                    datasets = [
                        {
                            label: "empirisch", 
                            data: dataset.data
                        }
                     ],
                    minmax = extrema(dataset.data);
                options = options || {};
                console.log(minmax.min[1], ' - ', minmax.max[1]);
                
                
                //console.log('Extrema', dataset.data[minmax.min[0]], dataset.data[minmax.max[0]]);

                // calculate probality
                if (options.prob !== false) {
                    e = prob(dataset.data);
                }

                container = $("<div/>", {
                    id: "plot-" + id,
                    style: "width: 500px; height: 500px;",
                    className: "plot"
                });
                
                $("<fieldset/>", {
                    html: "<legend>" + id + "</legend>" +
                          'E(X) = ' + e 
                }).append(container).appendTo($('body'));
                
                /*
                if (options.prob !== false) {
                    datasets = $.merge(datasets, [{
                       label: "exp", 
                       data: distributions.exponentiel.build(minmax.min[1], minmax.max[1], grouping, 50000, {a: -2}),
                       lines: {show: true}
                   },
                   {
                       label: "lognormal", 
                       data: distributions.lognormal.build(minmax.min[1], minmax.max[1], grouping, 100000, {a: -3}),
                       lines: {show: true}
                   },
                   {
                       label: "square", 
                       data: distributions.square.build(minmax.min[1], minmax.max[1], grouping, 50000, {a: 1}),
                       lines: {show: true}
                   },
                   {
                       label: "trend", 
                       data: distributions.trend.build(minmax.min[1], minmax.max[1], grouping, 50000, {a: 1}),
                       lines: {show: true}
                   }]);
                }//*/
                $.plot($("#plot-" + id), datasets, {
                    points: {show: true},
                    grid: {hoverable: true, clickable: true}
                });   
                
                $("#plot-" + id).bind('plothover', function (event, pos, item) {
                    if (item) {
                        tooltip(item.pageX, item.pageY, '(' + item.datapoint[0] + '|' + item.datapoint[1] + ')')
                    } else {
                        $('#tooltip').hide();
                    }
                });
            },
            tooltip = function (x, y, text) {
                var container = $('#tooltip');
                
                container.css({
                    top: y + 5,
                    left: x + 5
                }).text(text).show();
            },
            sample_files = ['bloodsamples_1.json', 'bloodsamples.json'],
            sample_rows = [],
            sample_jxhr = [];
         
        //init 
        for (id in datasets) {
            datasets[id].data = [];
            datasets[id].e = 0;
        }
        
        $.each(sample_files, function (i, file) {
            sample_jxhr.push($.getJSON('http://localhost/js/grease/slmania/data/' + file, function (data) {
                sample_rows = $.merge(sample_rows, data.npcs);
            }));
        });

        $.when($.getJSON('http://localhost/js/grease/backend/npcs.php'),
               sample_jxhr).
        then(function(a, b) {
            var npcs = a[0].npcs,
                rows = sample_rows,
                last_samples = {},
                tests = [],
                intervaltest;
             
            // loop through each entry and filter the entries
            $.each(rows, function (i, data) {
                var ignore = false;

                data.reward -= data.reward % grouping;

                if (data.reward > 0 && data.name !== undefined) { // get information from database
                    if (npcs[data.name] !== undefined) {
                        data.special |= (npcs[data.name].unique_npc << 1);
                        if (data.dungeon === undefined) {
                            data.dungeon = (!npcs[data.name].jumper && npcs[data.name].pos_x < 0 && npcs[data.name].pos_y < 0);
                        }
                        if (data.aggressive == undefined) {
                            data.aggressive = npcs[data.name].aggressive;
                        }
                    } else if (!(data.special&1)) {
                        ignore = true;
                    }
                } else {
                    ignore = true;
                }

                // check where to fill in the reward
                for (id in datasets) {
                    if (ignore === false && datasets[id].filter(data) === true) {
                        if (id === 'Bonus') {
                            if (npcs[data.name].count >= 2) {
                                //datasets[id].data.push([data.bonus[0] / npcs[data.name].count, npcs[data.name].count]);
                                datasets[id].data.push([npcs[data.name].count, data.bonus[1]]);
                            }
                        } else {
                            push(datasets[id].data, data.reward, 1);
                        }
                    }
                }
                
                intervaltest = 300000;
                if (data.name && data.date > 0 && data.reward > 0) {
                    // check the reward of the previous sample of this npc
                    if (
                        last_samples[data.name] !== undefined && 
                        last_samples[data.name].reward === data.reward
                       ) {
                        
                        tests.push([i+': '+data.name + '>'+(data.date - last_samples[data.name].date), data.date - last_samples[data.name].date < intervaltest]);
                    }
                    
                    last_samples[data.name] = {
                        date: +data.date,
                        reward: data.reward
                    };
                }
            });
            /*
            console.log('true: ', $.grep(tests, function (value) {
                return value[1];
            }).length, 'false: ', $.grep(tests, function (value) {
                if (!value[1]) {
                    console.log(value);
                }
                return !value[1];
            }).length);
            console.log(tests);//*/
            
            for (id in datasets) {
                console.log(id);
                create_plot(datasets[id], {
                   prob: id !== 'Bonus' 
                });
            }
        });
    });
}(window, jQuery));