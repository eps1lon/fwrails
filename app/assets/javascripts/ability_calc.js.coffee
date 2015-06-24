//= require lib/Ability

$(document).ready ->
  create_row = (name, basetime) ->
    # defaults
    id = new_ability_id null
    name ||= "ability(" + id + ")"
    basetime ||= 0
    
    # clone row
    $new_row = $("table#abilities tbody tr:first").clone()
    
    # insert new values
    # id, data.id changes when inspecting in in javascript but dom doesnt show it
    $new_row.data('id', id)
    $(".name input[type='hidden']", $new_row).val id
    # name and desc
    $(".name em", $new_row).text name
    $(".name span", $new_row).text ""
    # basetime
    $(".basetime input", $new_row).val basetime
    
    # and prepend it to lerntechnik
    $("table#abilities tr[data-id='3']").before $new_row
    
    # val doesnt call change function or what am i missing?
    $(".basetime input", $new_row).change()
    
    $new_row
  
  get_ability_row = (name) ->
    row = ($(container).parents("tr") for container in $("table#abilities td.name em") when $(container).text() == name)
    
    # found
    if row.length > 0
      row[0]
    # not found => create row
    else 
      create_row(name, 0)
      
  new_ability_id = (old_id) ->
    inputs = $("#abilities td.name input[type='hidden']")
    # inputs.val() should return array but returns only last value
    # so we reduce the array
    ids = inputs.toArray().reduce (ids, input) ->
      ids.push ~~$(input).val()
      ids
    , []
    
    # start with 0 - 1
    ids.push 0
    
    # Math.min(ids) returns NaN despite being an array of numbers
    old_id || (Math.min.apply(Math, ids) - 1)
  
  # shift from_stage to till_stage to display what time we already spent
  ability_calc = {
    time_spent_mode: () ->
      $(this)
      
      # shift values
      till_stage = ~~$("tr.ability td.till_stage input").val ->
        $("td.from_stage input", $(this).parents("tr")).val()
  
      # and set from stage to 1 or 0 if we dont have to learn anything
      $("tr.ability td.from_stage input").val Math.min(1, till_stage)
    
    new_ability: () ->
      create_row null, 0
    
    # calc learntime
    calc: () ->
      $this = $(this)
      
      # get lt stage
      $lt_row = $("tr.ability[data-id='3']")
      lt_start = ~~$(".from_stage input", $lt_row).val()
      lt_end = ~~$(".till_stage input", $lt_row).val()

      # sum times
      [learntime_min_sum, learntime_max_sum] = [0, 0]

      $("table#abilities tr.ability").each ->
        $this = $(this)

        # init ability
        id = ~~$this.data("id")
        basetime = ~~$(".basetime input", $this).val()
        ability = new Ability(id, basetime)

        # get learntime interval
        from_stage = ~~$(".from_stage input", $this).val()
        till_stage = ~~$(".till_stage input", $this).val()

        # calculate learntime
        learntime_min = ability.learntime_till(from_stage, till_stage, lt_end)
        learntime_max = ability.learntime_till(from_stage, till_stage, lt_start)

        # and display it 
        $(".learntime_min", $this).text countdown(learntime_min)
        $(".learntime_max", $this).text countdown(learntime_max)

        # and add it to sum
        learntime_min_sum += learntime_min
        learntime_max_sum += learntime_max

      # display_sum
      $("#learntime_min_sum").text countdown(learntime_min_sum)  
      $("#learntime_max_sum").text countdown(learntime_max_sum) 
  }
  
  # basetime has changed -> display new countdown string
  # since we clone rows later on we need to delegate 
  $("table#abilities tbody").on "change", ".basetime input", ->
    $this = $(this)
    s = +$this.val() # intcast
    
    $this.next(".time_distance").text(countdown(s))
  
  # calling click via jquery doesnt set focus so we need to do it manually
  $("#ability_calc input[type='submit']").click ->
    $(this).focus()
  
  # ability_calc submit
  $("#ability_calc").submit -> 
    $this = $(this)

    # delegate 
    fn  = $(this).find("input[type=submit]:focus" ).prop("name");
    ability_calc[fn].apply this, null
    
    # preventDefault
    false
    
  $("#abilities_cp").submit ->
    text = $("#abilities_ingame").val()
    
    regex = /([äöüÄÖÜ\w\-]+)\t(\d+)(\t(\d+))?/
    
    lines = text.match(new RegExp(regex.source, "g"))
    
    for line in lines
      match = line.match regex
      
      [name, from_stage, till_stage] = [match[1], ~~match[2], ~~match[4]]
      # undefined casts to 0 => ability fully learned
      till_stage ||= from_stage
      
      # get row
      $row = get_ability_row name
      
      # insert stages
      $(".from_stage input", $row).val from_stage
      $(".till_stage input", $row).val till_stage
    
    # and calc
    $("#ability_calc input[type='submit'][name='calc']").click()
    
    # preventDefault
    false
    