/*jslint devel: true, browser: true, continue: true, nomen: true, regexp: true, maxerr: 50, indent: 4 */
/**
 * fill in your personal user_id
 */
var user_id = '721366d55104bb1765173fe40895a29cf76c7dae';

(function (window, __undefined) {
    "use strict";
    var $ = window.jQuery,
        stats = {
            0: {
                basetime: 2592000,
                factor: 1.19,
                name: "Fischzucht",
                depot: true,
                produce_x: 1,
                produce_y: 1,
                depot_x: 1,
                depot_y: 1
            },
            1: {
                basetime: 13000000,
                factor: 1.04,
                name: "Getreide",
                depot: true,
                produce_x: 1,
                produce_y: 1,
                depot_x: 1,
                depot_y: 1
            },
            2: {
                basetime: 2592000,
                factor: 1.2,
                name: "Ölturm",
                depot: true,
                produce_x: 1,
                produce_y: 1,
                depot_x: 1,
                depot_y: 1
            },
            3: {
                basetime: 2592000,
                factor: 1.2,
                name: "Sumpfgas",
                depot: true,
                produce_x: 1,
                produce_y: 1,
                depot_x: 1,
                depot_y: 1
            },
            13: {
                basetime: 75000,
                factor: 1.00,
                name: "Chaoslabor",
                depot: false,
                produce_x: 1,
                produce_y: 1
            }
        },
        uStats = {},
        stat_get = function (current, dummy) {
            var epsilon = Math.pow(10, -10),
                now = +(new Date()),
                current = ((now - current.timestamp) / dummy.basetime) * Math.pow(dummy.factor, current.stage);
            
            if (dummy.depot === true) {
                return Math.floor(current);
            } else {
                return current > (1 - epsilon);
            }
        },
        stat_set = function (current, dummy, amount) {
            var now = +(new Date());
            
            amount = amount || 0;
            
            return now - (dummy.basetime * amount) / Math.pow(dummy.factor, current.stage);
        };
    
    if (window.location.href.match(/stats\.php/)) {
        uStats = JSON.parse(localStorage.getItem('slmania-stats')) || {};
        
        $('p.listrow > table').each(function (i, table) {
            console.log(table); 
        });
    }
    
    window.stats = stats;
    window.stat_get = stat_get;
    window.stat_set = stat_set;
}(window));