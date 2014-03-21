(function (window) {
    'use strict';
    var document = window.document;
    
    $(document).ready(function () {
        var sample_files = ['bloodsamples_1.json', 'bloodsamples.json'],
            sample_rows = [];;

        $.each(sample_files, function (i, file) {
            $.ajax({
                async: false,
                dataType: 'json',
                url: 'http://localhost/js/grease/slmania/data/' + file,
                success: function (data) {
                    sample_rows = $.merge(sample_rows, data.npcs);
                }
            });
        });

        $.when(true).then(function () {
            var rare_count = 0,
                sample_count = 0,
                rare_npcs = ['Goldhornziege', 'Schatzsucher', 'Erdvogel', 
                             'Eisvogel', 'Feuervogel', 'Erd-Skelkos', 
                             'bulliges Erd-Skelkos', 'reisender Fallensteller',
                             'Dämonenhund', 'Thorom Logrid', 'Dunbrakatze',
                             'Spezialist für Erze', 'rote Landkoralle', 
                             'schwebende Goldkutsche', 'Weltenwandler',
                             'Schattenkreatur Mantori', 'Schattenkreatur Jalakori',
                             'Schattenkreatur Gortari', 'Schattenkreatur Turwakori'],
                mutation_count = 0;

            $.each(sample_rows, function (i, data) {
                if (data.special&1) {
                    mutation_count += 1;
                }

                if (data.name) {
                    sample_count += 1;

                    if (data.special&1) {
                        data.name = data.name.split('-').slice(1).join('-');
                    } 

                    if ($.inArray(data.name, rare_npcs) !== -1) {
                        rare_count += 1;
                    }
                }
            });

            $('<div>variable NPC: ' + rare_npcs.join(',') + '<br>Proben: ' + sample_count + '<br>variabel: ' + rare_count + '</div>').appendTo(document.body);
            $('<div>mutiert: ' + mutation_count + '<br>Proben: ' + sample_rows.length + '</div>').appendTo(document.body);
        })
    });
}(this));

