(function ( window, undefined__ ) {
  'use strict';
  
  var document = window.document,
      api_url = 'shop_api.php',
      api = function ( data, done_cb ) {
        $.getJSON( api_url, data )
          .done( function ( json ) {
            if ( json.error !== undefined__ ) {
              console.log( 'api_error: ' + json.error );
            } else {
              done_cb( json );
            }
          } )
          .fail ( function () {
            console.log('something went terribly wrong: ');
            console.log(this);
          } );
      },
      COUNTDOWN_ZERO_FILL_BEFORE = 1,
      COUNTDOWN_ZERO_FILL_AFTER = 2,
      COUNTDOWN_ZERO_FILL = COUNTDOWN_ZERO_FILL_BEFORE|COUNTDOWN_ZERO_FILL_AFTER,
      cd_string = function (diff, separator, level, gr_case, flags) {
        var parts = [
              {
                  name: 'Tag',
                  amount: 86400,
                  plural: ['e', 'en']
              },
              {
                  name: 'Stunde',
                  amount: 3600,
                  plural: ['n', 'n']
              },
              {
                  name: 'Minute',
                  amount: 60,
                  plural: ['n', 'n']
              },
              {
                  name: 'Sekunde',
                  amount: 1,
                  plural: ['n', 'n']
              }                        
            ],
            cd_array = [],
            cd_num   = 0,
            cd_sum   = 0,
            mod      = 0;

        gr_case = ((gr_case) ? +gr_case : 0);
        level   = ((level) ? level : parts.length);

        for (var i = 0; i < parts.length; ++i) {
            cd_num = Math.floor(((mod > 0) ? ((diff % mod) / parts[i].amount) : (diff / parts[i].amount)));
            cd_sum += cd_num;
            if (cd_num ||
                (flags&COUNTDOWN_ZERO_FILL_BEFORE && !cd_sum) ||
                (flags&COUNTDOWN_ZERO_FILL_AFTER  &&  cd_sum)
               ) {
                cd_array.push(cd_num + ' ' + parts[i].name + ((cd_num != 1) ? parts[i].plural[gr_case] : ''));
            }
            mod = parts[i].amount;
        }
        return cd_array.slice(0, level).join(separator);   
    };
  
  $( document ).ready( function () {
    api( 'do=full', function ( json ) {
      var shops = json.shops;
      
      //console.log(shops);
      
      $( '#shops caption' ).text( 'Shops' );
      $( '#shops' ).append( '<thead><tr>' +
                              '<th class="name">Name</th>'          +
                              '<th class="position">Position</th>'  +
                              '<th class="factor">E-Faktor</th>'    +
                              '<th class="factor">V-Faktor</th>'    +
                              '<th class="items">Items</th>'        +
                              '<th class="changes_in">Wechsel</th>' +
                            '</thead></tr>' );
      
      $.each( shops, function ( i, shop ) {
        var row = $( '<tr/>', {
                      'data-x': shop.x,
                      'data-y': shop.y,
                      click: function () {
                        var x = +$( this ).data( 'x' ),
                            y = +$( this ).data( 'y' ),
                            shop = null,
                            url = '';
                            
                        $.each( shops, function (i, row) {
                          if ( row.x == x && row.y == y ) {
                            shop = row;
                            return false;
                          }
                        } );
                        
                        if (shop !== null) {
                          // clear
                          $( '#shop_details dd' ).text( '' );
                          
                          $( '#shop_details h1:first' ).text( shop.name );
                          
                          url = 'do=get_shop&shop_x=' + shop.x + '&shop_y=' + 
                                shop.y + '&intervall=+1';
                          api( url, function ( json ) {
                            var shop = json.shop;
                            
                            if ( typeof shop.buyrange === 'object' ) {
                              $( '#next_buyfactor' ).html( '<strong>' + shop.buyfactor + '</strong>' );
                            }
                            
                            if ( typeof shop.sellrange === 'object' ) {
                              $( '#next_sellfactor' ).html( '<strong>' + shop.sellfactor + '</strong>' );
                            }
                            
                            if ( typeof shop.current_items === 'object' ) {
                              $( '#next_items' ).html( '<strong>' + shop.current_items.join( '</strong>, <strong>' ) + '</strong>' );
                            }
                          } );
                          //console.log( shop );

                          // best buyfactor
                          if ( typeof shop.buyrange === 'object') {
                            url = 'do=lookfor_buyfactor&shop_x=' + shop.x + 
                                  '&shop_y=' + shop.y + 
                                  '&buyfactor=' + shop.buyrange[0];
                            api( url , function ( json ) {
                              //console.log(json.shops);
                              $( '#best_buyfactor' ).html( '<strong>' + json.shops[0].buyrange[0] + '</strong> ' + 
                                                           '<time>' + 
                                                           new Date(json.shops[0].time * 1000).toLocaleString() +
                                                           '</time>');
                            } );
                          }

                          // best sellfactor
                          if ( typeof shop.sellrange === 'object') {
                            url = 'do=lookfor_sellfactor&shop_x=' + shop.x + 
                                  '&shop_y=' + shop.y + 
                                  '&sellfactor=' + shop.sellrange[1];

                            api( url , function ( json ) {
                              //console.log(json.shops);
                              $( '#best_sellfactor' ).html( '<strong>' + json.shops[0].sellrange[1] + '</strong> ' + 
                                                           '<time>' + 
                                                           new Date(json.shops[0].time * 1000).toLocaleString() +
                                                           '</time>');
                            } );
                          }

                          if ( typeof shop.full_items === 'object' ) {
                            var list = $( '<ul/>' ).appendTo( '#items' );
                            $.each( shop.full_items, function ( i, item_name ) {
                              url = 'do=lookfor_item&item_name=' + encodeURI( item_name );

                              (function ( item_name ) {
                                api( url, function ( json ) {
                                  //console.log(json, url);
                                  list.append( '<li><strong>' + item_name + ':</strong> ' + 
                                               '<time>' + 
                                               new Date(json.shop_lookfor.time * 1000).toLocaleString() + 
                                               '</time></li>' );
                                } );
                              }( item_name ));

                            } );
                          }
                        }
                      }
                    } ),
            factor = 0.0;
        
        row.append( '<td class="name">' + shop.name + '</td>' );
        row.append( '<td class="position">X: ' + shop.x + ' Y: ' + shop.y + '</td>' );
        
        $.each( ['buyfactor', 'sellfactor'], function (i, factor) {
          var relative = 1,
              class_name = 'neutral'; 
          
          if ( shop[factor] === undefined__ ) {
            row.append( '<td class="factor undefined">-</td>' );
          } else {
            relative -= shop[factor];
            relative *= (factor == 'buyfactor' ? -1 : +1);
            
            if ( relative < 0 ) {
              class_name = 'positive';
            } else if ( relative > 0 ) {
              class_name = 'negative'
            } 
            
            row.append( '<td class="factor ' + class_name + '">' + shop[factor] +  '</td>' );
          }
        } );
        
        
        row.append( '<td class="items">' + (shop.current_items === undefined__ ? '-' : 
                                shop.current_items.join( ', ' )) + '</td>' );
        row.append( '<td class="changes_in">' + cd_string( shop.changes_in, ', ', 2 ) + '</td>' );
        
        row.appendTo( '#shops' );
      } );
      
      $('table').tablesorter({
        debug: false,
        headers: {
          1: {
            sorter: false
          },
          4: {
            sorter: false
          },
          5: {
            sorter: false
          }
        }
      });
    } );
    
    // nächste 15er
    api( 'do=lookfor_sellfactor&sellfactor=1.15', function ( json ) {
      $.each( json.shops, function ( i, shop ) {
        
        var li = $( '<li/>', {
                      'data-x': shop.x,
                      'data-y': shop.y,
                      click: function () {
                        $('tr[data-x="' + $( this ).data( 'x' ) + '"][data-y="' + $( this ).data( 'y' ) + '"]').click();
                      },
                      html: '<strong>' + shop.name + '</strong> ' + 
                            '<time>' + new Date(shop.time * 1000).toLocaleString() + 
                            '</time>'
                   });
        $( '#next_best_shops' ).append( li );
      } );
    } );
  });
}(this));