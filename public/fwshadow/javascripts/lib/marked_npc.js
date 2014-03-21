'use strict';
var marked_npc = {};

(function (__undefined) {
    window.marked_npc = (function () {
        var marked_npc = JSON.parse(localStorage.getItem('slmania-marked_npc')) || {};
        
        return {
            add: function (name, color, onSuccess, extend) {
                var self = this;
                extend = extend || {};
                
                $.getJSON('http://fwrails.net/fwshadow/backend/npcsearch.php', {name: extend.name || name}, function (data) {
                    console.log(data);
                    marked_npc[name] = $.extend({
                        color: color
                    }, data, extend);

                    if (typeof onSuccess === 'function') {
                        onSuccess();
                    }
                    self.save();

                    return true;
                });
            },
            del: function (name) {
                var deleted = delete marked_npc[name]; // delete prop and keep whether that was successful
                this.save();
                
                return deleted;
            },
            get: function () {
                return marked_npc;
            },
            save: function () {
                localStorage.setItem('slmania-marked_npc', JSON.stringify(marked_npc));
            }
        };
    }());
}());