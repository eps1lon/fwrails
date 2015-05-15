$(document).ready ->
  # generates pos_key according to Place.pos_key
  pos_key = (x, y) ->
    [x, y].join "_"
  
  # gets div for place (x, y)  
  place = (x, y) ->
    $("#minimap .place#place" + pos_key(x, y))
  
  # toggle all npcs
  $("#toggle_all_npcs").change ->
    # toggle all npcs according to this state
    $(".toggle_npc").prop "checked", $(this).prop("checked")
    
    #doesnt fire change event!
    $(".toggle_npc").change()
  
  # toggle npc highlight in map
  $(".toggle_npc").change ->
    # get npc
    npc = (npc for npc in npcs when npc.id is +$(this).val())[0]

    # and toggle the `highlight` class according to checked state
    place(npc.pos_x, npc.pos_y).toggleClass("highlight", $(this).prop("checked"))
      