(function (window, undefined) {
    window.onerror = function () {};
    var document = window.document,
        last_chat = 0,
        alarm = (function () {
            var html_id = 'slmania-alarm-sound',
                file = '../sounds/chatalarm.ogg';
            
            return {
                on: function () {
                    $('<audio/>', {
                        id: html_id,
                        loop: true,
                        controls: true,
                        autoplay: false,
                        src: chrome.extension.getURL(file)
                    }).appendTo($('#slmania-content'));
                },
                off: function () {
                    $('#' + html_id).remove();
                },
                play: function () {
                    return $('#' + html_id)[0].play();
                },
                stop: function () {
                    return $('#' + html_id)[0].stop();
                }
            };
        }()),
        constant = function (name) {
            return window[name.toUpperCase()];
        },
        define = function (name, value) {
            name = name.toUpperCase();
            
            if (window.__lookupGetter__(name) === undefined && window.__lookupSetter__(name) === undefined) {
                window.__defineGetter__(name, function () {
                   return value; 
                });
                
                return true;
            }
            
            return false;
        },
        observer = function (chat) {
            if (chat.type === CHAT_INFO) {
                if (chat.sub_type === CHAT_INFO_STONEWORM) {
                    alarm.play();
                }
            }
        },
        init_interface = function () {
            $('#slmania-body').remove();
            
            // Log Interface erstellen
            create_interface().prependTo($('body'));
            
            $('#slmania-content').append($('<input/>', {
                id: 'slmania-chat-alarn',
                type: 'checkbox',
                title: 'Chatlaram an/aus',
                checked: localStorage.getItem('slmania-chat-alarm') ? true : false,
                click: function () {
                    if (this.checked) {
                        alarm.on();
                    } else {
                        alarm.off();
                    }

                    localStorage.setItem('slmania-chat-alarm', this.checked ? 1 : '');
                }
            }));

            if (localStorage.getItem('slmania-chat-alarm')) {
                alarm.on();
            }
        },
        Chat = function (html) {
            var chat = $(html),
                matches = [];
            
            switch (chat.attr('class')) {
                case 'chattext':
                    this.type     = CHAT_LOCAL;
                    this.sub_type = CHAT_LOCAL_OTHER;
                    
                    break;
                case 'chattextinfo':
                    this.type     = CHAT_INFO;
                    this.sub_type = CHAT_INFO_OTHER;
                    this.text = chat.text();
                    
                    if (this.text == 'Erschütterungen deuten darauf hin, dass sich ein Schotterwurm Laree nähert.') {
                        this.sub_type = CHAT_INFO_STONEWORM;
                    }
                    
                    break;
                case 'chattextclan':
                    this.type     = CHAT_CLAN;
                    this.sub_type = CHAT_CLAN_OTHER;
                    
                    matches = chat.text().match(/(.+?) \(Clantelepathie\): (.*)/) || [];
                    
                    if (matches.length > 2) {
                        this.sub_type = CHAT_CLAN_TELE;
                        
                        this.sender = matches[1];
                        this.text   = matches[2];
                    } else {
                        
                    }
                    
                    break;
                case 'chattextgroup':
                    this.type     = CHAT_GROUP;
                    this.sub_type = CHAT_GROUP_OTHER;
                    
                    matches = chat.text().match(/(.+?) \(Gruppentelepathie\): (.*)/) || [];

                    if (matches.length > 2) {
                        this.sub_type = CHAT_GROUP_TELE;
                        
                        this.sender = matches[1];
                        this.text   = matches[2];
                    } else {
                        this.text = chat.text();
                    }
                    
                    break;
                case 'chattextscream':
                    this.type = CHAT_SCREAM;
                    
                    matches = chat.text().match(/(.+?) schreit: (.*)/) || [];
                    
                    if (matches.length > 2) {
                        this.sender = matches[1];
                        this.text   = matches[2];
                    }
                    
                    break;
                case 'worldsay':
                    this.type     = CHAT_WORLDSAY;
                    this.sub_type = CHAT_WORLDSAY_OTHER;
                    
                    this.text = chat.text();
                    
                    if (this.text == 'Die dunkle Zusammenkunft der Taruner, dunklen Magier und Serum-Geister hat jetzt die meisten Kontrolltürme erobert und herrscht über diese Welt.') {
                        this.sub_type = CHAT_WORLDSAY_FRACTION_TO_RED;
                    } else if (this.text == 'Das Bündnis der Kämpfer, Arbeiter, Zauberer, Onlos und Natla hat jetzt die meisten Kontrolltürme erobert und herrscht über diese Welt.') {
                        this.sub_type = CHAT_WORLDSAY_FRACTION_TO_BLUE;
                    }
                    
                    break;
                default:
                    this.type = CHAT_OTHER;
                    break;
            }
        };
    
    Chat.prototype.is = function (type) {
        return constant('CHAT_' + type.toUpperCase()) == this.type;
    }
    
    Chat.prototype.is_sub = function (sub) {
        return constant('CHAT_' + this.type_to_s + '_' + sub) == this.sub_type;
    }
    
    Chat.prototype.type_to_s = function () {
        return Chat.prototype.types[this.type - 1];
    }
    
    Chat.prototype.sub_type_to_s = function () {
        return Chat.prototype.sub_types[this.type_to_s()][this.sub_type - 1];
    }
    
    Chat.prototype.toString = function () {
        var text = '';
        
        text += 'type: ' + this.type_to_s();
        text += '; sub_type: ' + this.sub_type_to_s();
        text += '; text: ' + this.text;
        
        return text;
    }
    
    Chat.prototype.types = ['local', 'scream', 'clan', 'group', 'private', 'info', 'worldsay', 'other'];
    Chat.prototype.sub_types = {
        'local': ['neutral', 'text', 'other'],
        'scream': [],
        'clan': ['tele', 'other'],
        'group': ['tele', 'insp', 'other'],
        'private': [],
        'info': ['stoneworm', 'other'],
        'worldsay': ['fraction_to_red', 'fraction_to_blue', 'other'],
        'other': []
    };
    
    // define chat types
    $.each(Chat.prototype.types, function (i, type) {
       define('CHAT_' + type, i + 1);
       
       // and sub_types
       $.each(Chat.prototype.sub_types[type], function (j, sub) {
          define('CHAT_' + type + '_' + sub, j + 1); 
       });
    });
    
    init_interface();
    
    $('.framechattextbg p').each(function (i, html) {
        last_chat = i;
        observer(new Chat(html));
    });
    
    $(window).scroll(function () {
        init_interface();
        
        $('.framechattextbg p').slice(last_chat).each(function (i, html) {
            last_chat += 1;
            observer(new Chat(html));
        });
    });
}(window));