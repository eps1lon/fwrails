OBJECT2FORM = false
FORM2OBJECT = true

$current_place = null

npcs = []

add_npc_entry = (npc) ->
  $li = $('<li/>', {
    data: {
      npc_id: npc.id
    },
    html: npc.name
  })
  
  $li.append $('<input/>', {
    click: ->
      edit_npc_entry $(this).parent().data('npc_id')
    type: 'button'
    value: 'edit'
  })
  
  $('#npcs').append $li

attr_from_id = (id, object_name) ->
  id.replace object_name + '_', ''
  # id.replace /^`object_name`_/, ''
  #id.substr object_name.length + 1

change_npc = (callback) ->
  npc = search(npcs, +$('#npc_id').val(), 'id')[0]
    
  if !npc then flash('no npc edited') else
    callback npc
    
    npcs_changed()

edit_npc_entry = (id) ->
  npc = search(npcs, id, 'id')[0]
  
  if !npc then flash('not found') else edit_npc npc

edit_npc = (npc) ->
  $('#npc_id').val npc.id
  
  # first npc values
  object_to_form $('#npc_form form'), npc, 'npc', OBJECT2FORM
  
  # clear drop forms
  # we could just remove them via dom manip but we want to to keep one and clear
  # the values. .remove_drop already can achiev this
  $('#npc_form .association.drop').each ->
    $('#npc_form .remove_drop').click()
    
  # then add drops
  for drop, i in npc.drops
    $('#npc_form .add_drop').click() # add drop form
    
    # save values
    object_to_form $('#npc_form .association.drop:eq(' + i + ')'),
                    drop, 'npc_drop', OBJECT2FORM

empty = (val) ->
  !val || val == '0'

flash = (msg) ->
  console.log msg

int = (s) ->
  if isNaN(+s) then 0 else +s

new_npc_id = () ->
  Math.min(Math.min.apply(Math, (npc.id for npc in npcs)), 0) - 1

npcs_changed = () ->
  # update npcs on selected field
  $current_place.click() unless $current_place == null

object_to_form = (form, object, object_name, reverse) ->
  reverse ||= false
  
  # form > .field > input
  $(form).children('.field').children('input').each ->
    $this = $(this)

    # extract attr
    attr = attr_from_id $this.attr('id'), object_name

    if reverse # form2object
      object[attr] = $this.val()
    else # object2form
      $this.val object[attr]

search = (array, val, attr) ->
  elem for elem in array when elem[attr] == val

array_sum = (array, attr) ->
  if attr
    array.reduce ((a,b) -> a + int(b[attr])), 0
  else
    array.reduce ((a,b) -> a + int(b)), 0

$(document).ready ->
  npcs = window.npcs || []
  
  # calculate
  $('#calculate'). click ->
    $table = $('<table/>')
    $table.append $('<thead><tr><th>NPC</th><th>Golddrop</th><th>Items</th></tr></thead>')
    
    global_drops = []
    
    $('.drop > form').each ->
      drop = {}
      object_to_form(this, drop, 'drops_npc', FORM2OBJECT)
      global_drops.push drop unless empty(drop.drop_id)
      
    # Perle der Angst als globalen Drop
    pearl = search global_drops, 'Perle der Angst', 'drop_id'
    
    # charas
    plunder_stage = $('#plunder_stage').val()
    chase_stage = $('#plunder_stage').val()
    
    sum = {
      drops: 0,
      gold: 0,
      npcs: npcs.length
      xp: 0
    }
    
    # clear result
    $('#result').empty()
    
    for npc in npcs
      $row = $('<tr/>')
      
      # npc identifier
      $cell = $('<td>' + npc.name +  ' Position X: ' + npc.pos_x + ' Y: ' + npc.pos_y + '</td>')
      $row.append $cell
      
      # Golddrop
      if npc.action == 'chase' # verjagen
        gold = Math.floor(npc.gold * Math.pow(1.0025, chase_stage))
        $cell = $('<td>' + npc.gold + ' * 1.0025   ^ ' + chase_stage +  ' = ' + gold + '</td>')
      else
        gold = Math.floor(npc.gold * Math.pow(1.01, plunder_stage))
        $cell = $('<td>' + npc.gold + ' * 1.01 ^ ' + plunder_stage +  ' = ' + gold + '</td>')
        
      $row.append $cell  
      sum.gold += gold
      
      # Drops
      $cell = $('<td/>')
      
      # clone drops
      drops = $.extend [], npc.drops
      
      # keine npc-drops beim verjagen
      if npc.action == 'chase' 
        drops = []
        
        unless pearl == null
          pearl.chance = 0.008 * Math.log(npc.strength + 1) * Math.pow(1.01, chase_stage)
          drops << pearl
      else # globale npc-drops
      
      $drops = $('<table><thead><tr><th>Drop</th><th>Chance</th><th>Wert</th><th>Schnitt</th></tr></thead></table>')
      
      for drop in drops
        drop.avg = int(drop.chance) * int(drop.value)
        
        $drops.append '<tr><td>' + drop.drop_id + '</td>'+
                          '<td>' + (int(drop.chance) * 100) + '%</td>'+
                          '<td>' + int(drop.value) + '</td>'+
                          '<td>' + drop.avg + '</td></tr>'
                          
      $drops.append '<tfoot><tr><td></td>'+
                                '<td>' + array_sum(drops, 'chance') * 100 + '%</td>'+
                                '<td>' + array_sum(drops, 'value') + '</td>'+
                                '<td>' + array_sum(drops, 'avg') + '</td></tr></tfoot>'
      $cell.append $drops
      
      $row.append $cell
      sum.drops += array_sum drops, 'avg'
      
      $row.appendTo $table
      
    
    $table.append '<tfoot><tr><td>Summe</td>'+
                             '<td>' + sum.gold + '</td>'+
                             '<td>' + sum.drops + '</td></tr></tfoot>'
    
    $table.appendTo $('#result')
  
  # add npc
  $('#npc_add').click ->
    npc = {id: new_npc_id()}
    
    $('#npc_id').val npc.id
    npcs.push npc
    
    $('#npc_save').click()
    
    console.log npcs
  
  # dupe npc
  $('#npc_dupe').click ->
    
    change_npc (npc) ->
      # clone npc
      new_npc = $.extend {}, npc
      
      # give em a new id
      new_npc.id = new_npc_id()
      
      # and add it
      npcs.push new_npc
    
  # remove npc
  $('#npc_remove').click ->
    change_npc (npc_to_remove) ->
      npcs = $.grep npcs, (npc) ->
        npc_to_remove.id != npc.id
  
  # save npc
  $('#npc_save').click ->
    change_npc (npc) ->
      # first save npc values
      object_to_form $('#npc_form form'), npc, 'npc', FORM2OBJECT
      
      # then drops
      npc.drops = [] # clear first
      $('.association.drop').each ->
        drop = {} # empty drop
        
        # save values
        object_to_form this, drop, 'npc_drop', FORM2OBJECT
        
        # and add to rop pool
        npc.drops.push drop unless empty(drop.drop_id)
      
  # add drop
  $('.add_drop').click ->
    # clone with event handlers
    $new_drop_form = $('.association.drop:first').clone true
    
    # but without values
    $('.field > input', $new_drop_form).val ''

    $(this).before $new_drop_form
    false
    
  # remove drop
  $('.remove_drop').click ->
    # remove drop form
    if $('.association.drop').length > 1 
      $(this).parents('#npc_form .association.drop').remove()
    # but keep one scaffold
    else
      $('input', $(this).parents('.association.drop')).val('')
      
    false
  
  # place info
  $('.place').click ->
    $place = $(this)
    $current_place = $place
    
    # truncate list
    $('#npcs').empty()
    
    # add npcs of the current field
    add_npc_entry npc for npc in npcs when +npc.pos_x == $place.data('x') && +npc.pos_y == $place.data('y')
    
  # debug
  #$('.place[data-x="-336"][data-y="-719"]').click()
  #$('#npcs li:first input').click()
  #$('#calculate').click()
  
        