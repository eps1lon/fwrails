(function (window, __undefined) {
    var document = window.document,
        $ = window.jQuery,
        achievements = [],
        list_achieved = $('<ul/>', {
            id: 'achieved_list',
            'class': 'achievements'
        }),
        list_unachieved = $('<ul/>', {
            id: 'unachieved_list',
            'class': 'achievements'
        });
        
    $('.itemlistcaption, .listcaption').each(function (i, row) {
        var achievement = {
                name: '',
                desc: '',
                img: '',
                achieved: true
            },
            achievtable = null;
        
        if ($('a[name^="achiev"]', row).length > 0) { // is_achievement?
            row = $(row);
            achievtable = row.next('p.listrow').children('.achievtable')[0];
            
            achievement.name = row.text().trim();
            achievement.desc = $('td:last-child', achievtable).text().trim();
            achievement.img = $('.achievimg', achievtable).attr('src');
            achievement.achieved = !$('a[href*="toggletracking"]', row).length;
            
            if (achievement.achieved === true) {
                achievement.name = achievement.name.split(' - ').slice(0, -1).join(' - '); // "- Herumzeigen" abschneiden
            }
            
            achievements.push(achievement);
            
            //console.log(row.nextUntil('.itemlistcaption, .listcaption, .maincaption2'));
            row.nextUntil('.itemlistcaption, .listcaption, .maincaption2').remove();
            row.remove();
        }
        
        return true;
    });   
    
    
}(this));