(function () {
    'use strict';
    window.$SERVER = (function () {
        var server = {
                anchor: {},
                params: {},
                query_string: '',
                url: window.location.href
            },
            split = [];
            
        // fetch params
        if (window.location.href.indexOf('?') !== -1) {
            server.query_string = window.location.href.split('?')[1];
            
            if (server.query_string.indexOf('#') !== -1) {
                split = server.query_string.split('#');
                server.query_string = split[0];
                server.anchor = split[1];
            }
            
            $(server.query_string.split("&")).each(function (i, param) {
                var split = [];

                split = param.split("=");
                server.params[split[0]] = split[1];
            });
        }
        
        return server;
    }());
    
    window.create_interface = function () {
        return $('<div/>', {
            id: 'slmania-body'
        }).append($('<div/>', {
            id: 'slmania-transparent-layer'
        }), $('<div/>', {
            id: 'slmania-content'
        }));
    }
    
    Array.prototype.find = function (val, offset, strict) {
        var i = offset || 0,
            length = this.length;
        
        // ignore D.R.Y. to check only once for strict
        if (strict === true) {
            for (i; i < length; i += 1) {
                if (this[i] === val) {
                    return i;
                }
            }
        } else {
            for (i; i < length; i += 1) {
                if (this[i] == val) {
                    return i;
                }
            }
        }
        
        return -1;
    };
}());