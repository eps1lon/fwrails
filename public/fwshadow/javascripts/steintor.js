var inc = 0;
(function (window, undefined) {
    'use strict';
    var check_solution = function (gate, solution) {
            var i, j;
            
            for (i = 0; i < gate.length; i += 1) {
                for (j = 0; j < gate[i].length; j += 1) {
                    if (sgn(gate[i][j]) !== sgn(solution[i][j])) {
                        return [i, j];
                    }
                }
            }
            
            return true;
        },
        col_combinations = function (dimensions, result) {
            var combinations = [],
                empty = true,
                i, j,
                height = -1,
                new_dimensions,
                new_result;
            
            // init
            if (result === undefined) {
                result = [];
            }
            
            // jede zusammenhängende Masse durchlaufen
            for (i = 0; i < dimensions.solution.length; i += 1) {
                height = dimensions.solution[i].height;
                
                // Masse besteht aus Steinen und hat noch eine Höhe
                if (dimensions.solution[i].id === 1 && height > 0) {
                    empty = false; // weitere Kombinationen möglich > Rekursion
                    
                    // Jeden Möglichen Stein durchlaufen
                    for (j = 0; j < dimensions.userset.length; j += 1) {
                        if (dimensions.userset[j].height <= height) { // Stein passt
                            // Kopie anlegen
                            new_dimensions = dimensions; 
                            new_result     = result;
                            
                            // Stein abziehen
                            new_dimensions.solution[i].height -= dimensions.userset[j].height
                            // aus dem Topf nehmen
                            new_dimensions.userset.splice(j, 1)
                            //delete new_dimensions.userset[j];
                            
                            // Stein zu den Möglichkeiten legen
                            new_result.push(dimensions.userset[j]);
                            
                            // und rekursiv weitertesten
                            combinations = col_combinations(new_dimensions, new_result);
                        }
                    }
                }
            }
            
            if (empty === true) {
                combinations.push(result);
            }
            
            return combinations;
        },
        delete_piece = function (gate, piece) {
            var neighbors = piece_top_neighbors(gate, piece);
            
            reorder_piece_numeration(gate, piece, 0);
            
            fix_piece_positions(gate, neighbors);
        },
        display_gate = function (gate) {
            var cell,
                table = $('<table class="bgame"></table>'),
                i, j,
                rows = gate.length,
                cols = gate[0].length;
                
            for (i = 0; i < rows; i += 1) {
                $('<tr><td style="color: black; font-size: 1em;">' + (i+1) + '</td></tr>').appendTo(table);

                for (j = 0; j < gate[i].length; j += 1) {
                    cell = $('<td title="' + (i+1) + '-' + (j+1) + ': ' + gate[i][j] + '" data-i="' + i + '" data-j="' + j + '" data-piece="' + gate[i][j] + '"> </td>');

                    /*console.log('current ', i, j, ': ', gate.userset[i][j],, 
                                ' top: ',    gate.userset[i-1][j],
                                ' right: ',  gate.userset[i][j+1],
                                ' bottom: ', gate.userset[i+1][j]
                                ' left: ',   gate.userset[i][j-1]
                                );//*/

                    if (gate[i][j] === 0) {
                        cell.css('background-color', 'white');
                    } else {
                        // top
                        if (i === 0 || i > 0 && gate[i-1][j] !== gate[i][j]) {
                            cell.addClass('tb');
                        }
                        // right
                        if (j === cols - 1 || j < cols - 1 && gate[i][j+1] !== gate[i][j]) {
                            cell.addClass('rb');
                        }
                        // bottom
                        if (i === rows - 1 || i < rows - 1 && gate[i+1][j] !== gate[i][j]) {
                            cell.addClass('bb');
                        }
                        // left
                        if (j === 0 || j > 0 && gate[i][j-1] !== gate[i][j]) {
                            cell.addClass('lb');
                        }
                    }

                    cell.appendTo($('tr:last', table));
                }
            }
            
            $('td', table).click(function () {
                delete_piece(gate, +$(this).data('piece'));
                console.log($(this).parents('table'))
                $(this).parents('table').remove();
                display_gate(gate).addClass('b_userset').appendTo($('#userset_i'));
            });
            
            return table;
        },
        fix_piece_positions = function (gate, pieces) {
            var i, 
                moved = 1;
            
            //display_gate(gate).addClass('b_userset').appendTo($('body'));
            
            while (moved) {
                moved = 0;
                for (i = 0; i < pieces.length; i += 1) {
                    moved |= fix_piece_position(gate, pieces[i]);
                    //break;
                }
            } 
        },
        fix_piece_position = function (gate, piece) {
            var moved = 0,
                m = piece_messurements(gate, piece),
                old_top_neighbors = piece_top_neighbors(gate, piece);
            
            while (piece_bottom_neighbors(gate, piece).length === 0) {
                moved |= 1;
                push_down_piece(gate, piece);
            }
            
            if (moved) {
                fix_piece_positions(gate, old_top_neighbors);
            }
            
            return moved;
        },
        get_column = function (gate, col) {
            var column = [],
                i,
                old = -1;
            
            for (i = 0; i < gate.length; i += 1) {
                if (old !== gate[i][col]) {
                    column.push({
                       id: gate[i][col],
                       height: 0
                    });
                    
                    old = gate[i][col];
                }
                
                column[column.length-1].height += 1;
            }
            
            return column;
        },
        parse_gate = function (table, piece_increment) {
            var gate = [],
                piece = 0,
                visible = 0;

            $('tr', table).each(function (i, row) {
                gate[i] = [];

                $('td', row).each(function (j, cell) {
                    cell = $(cell);
                    
                    // unsichtbare haben feldfarbe im style attr
                    visible = +(cell.attr('style') === undefined);
                    
                    // neues Teilchen
                    piece = piece_increment(gate);
                    
                    if (visible) {
                        if (cell.hasClass('tb')) {
                            if (!cell.hasClass('lb')) {
                                piece = gate[i][j-1];
                            } 
                        } else if (i > 0 && gate[i-1][j] > 0) {
                            // fortsetzung von oben
                            piece = gate[i-1][j];
                            
                            if (!cell.hasClass('lb') && gate[i][j-1] > 0) { // links korrigieren
                                reorder_piece_numeration(gate, gate[i][j-1], piece);
                            }
                        } 
                    }
                    
                    piece *= visible;
                    
                    gate[i][j] = piece;
                    cell.attr('title', (i+1) + '-' + (j+1) + ': ' + piece);

                    // Teilchen wieder auf letztes setzen
                    //piece = recursive_max(gate.userset);
                });
            });
            
            return gate;
        },
        piece_messurements = function (gate, piece) {
            var i, j,
                messurements = {
                    x: [null, null],
                    y: [null, null],
                    height: 0,
                    width: 0
                };
            
            for (i = 0; i < gate.length; i += 1) {
                for (j = 0; j < gate[i].length; j += 1) {
                    if (gate[i][j] === piece) {
                        if (messurements.x[0] === null || j < messurements.x[0]) {
                            messurements.x[0] = j;
                        } else if (messurements.x[1] === null || j > messurements.x[1]) {
                            messurements.x[1] = j;
                        }
                        
                        if (messurements.y[0] === null || i < messurements.y[0]) {
                            messurements.y[0] = i;
                        } else if (messurements.y[1] === null || i > messurements.y[1]) {
                            messurements.y[1] = i;
                        }
                    }
                }
            }
            
            messurements.height = messurements.y[1] - messurements.y[0] + 1;
            messurements.width  = messurements.x[1] - messurements.x[0] + 1;
            
            return messurements;
        },
        piece_neighbors = function (gate, piece, topdown) {
            var neighbors = [],
                j, i;
                
            //console.log(messurements);
            
            for (i = 1; i < gate.length - 1; i += 1) {
                for (j = 0; j < gate[i].length; j += 1) {
                    if (gate[i][j] === piece && gate[i+topdown][j] > 0 && gate[i+topdown][j] !== piece) {
                        neighbors.push(gate[i+topdown][j]);
                    }
                }
            }

            return $.grep(neighbors, function(el, index){
                return index == $.inArray(el, neighbors);
            });
        },
        piece_bottom_neighbors = function (gate, piece) {
            return piece_neighbors(gate, piece, 1);
        },
        piece_top_neighbors = function (gate, piece) {
            return piece_neighbors(gate, piece, -1);
        },
        push_down_piece = function (gate, piece) {
            var i, j;
            for (i = gate.length - 1; i >= 0; i -= 1) {
                for (j = 0; j < gate[i].length; j += 1) {
                    if (i > 0 && gate[i][j] !== piece && gate[i-1][j] === piece) {
                        gate[i][j] = piece;
                    } else if (gate[i][j] === piece && (i === 0 || gate[i-1][j] !== piece)) {
                        gate[i][j] = 0;
                    }
                }
            }
        },
        recursive_max = function (a) {
            var i = 0, length = a.length,
                numbers = [];
            
            
            //console.log(a);
            for (i, length; i < length; i += 1) {
                //console.log(a[i], typeof a[i]);
                if (typeof a[i] === 'object') {
                    numbers.push(recursive_max(a[i]));
                } else {
                    numbers.push(a[i]);
                }
            }
            
            return Math.max.apply(Math, numbers);
        },
        reorder_piece_numeration = function (gate, search, replacement) {
            var i, j,
                cols = 0,
                rows = 0;
             
            for (i = 0, rows = gate.length; i < rows; i += 1) {
                for (j = 0, cols = gate[i].length; j < cols; j += 1) {
                    if (gate[i][j] === search) {
                        gate[i][j] = replacement;
                    }
                }
            }
        },
        row_dimensions = function (gate) {
            var i, j,
                dimensions = [], 
                old_type = -1;
            
            for (j = 0; j < gate[0].length; j += 1) {
                dimensions[j] = [];
                old_type = -1;
                
                for (i = 0; i < gate.length; i += 1) {
                    if (old_type == sgn(gate[i][j])) {
                        dimensions[j][dimensions[j].length-1] += 1;
                    } else {
                        old_type = sgn(gate[i][j]);
                        dimensions[j].push(1);
                    }
                }
            }
            
            return dimensions;
        },
        sgn = function (n) {
            if (n > 0) return  1;
            if (n < 0) return -1;
                       return  0;
        },
        first_diff = function (gate1, gate2, col) {
            var i = 0;
            
            for (i = 0; i < gate1.length; i += 1) {
                if (sgn(gate1[i][col]) !== sgn(gate2[i][col])) {
                    return i;
                }
            }
            
            return -1;
        },
        bottom_piece = function (gate, col) {
            var i;
            
            for (i = gate.length - 1; i >= 0; i -= 1) {
                if (gate[i][col] > 0) {
                    return gate[i][col];
                }
            }
            
            return -1;
        },
        solve_gate = function (origin, solution, blacklist, j) {
            var gate = origin,
                i,
                new_gate;
            
            if (blacklist === undefined) {
                blacklist = [];
            }
            
            if (j === undefined) {
                j = 0;
            }

            while (check_solution(gate, solution) !== true) {
                if (first_diff(gate, solution, j) > -1) {
                    delete_piece(gate, bottom_piece(gate, j));
                    
                    //solve_gate(gate, j);
                } else {
                    j += 1;
                }
                
                break;
            }
            
            return gate;
        },
        document = window.document;
    
    $(document).ready(function () {
        var bgame = {
                solution: $('.bgame.b_solve')[0],
                userset: $('.bgame.b_userset')[0]
            },
            gates = null;
            
        if (!!bgame.solution === true) {
            gates = {
                solution : parse_gate(bgame.solution, function () { 
                    return 1; 
                }),
                userset: parse_gate(bgame.userset, function (gate) {
                    var i = recursive_max(gate);
                    
                    if (isFinite(i) === false) {
                        return 1;
                    }
                    return i + 1;
                })//*/
            }
            
            //console.log(gates);
            
            display_gate(gates.userset).addClass('b_userset').appendTo($('#userset_i'));
            display_gate(gates.solution ).attr('id', 'gatecopy_solution' ).addClass('b_solve'  ).appendTo($('body'));
            
            solve_gate(gates.userset, gates.solution);
            
            display_gate(gates.userset).addClass('b_userset').appendTo($('body'));
            
        }
    });
    
    
}(this))