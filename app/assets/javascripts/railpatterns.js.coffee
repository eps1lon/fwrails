#Erfahrungsbindung
class window.Experienceabsorption extends Railpattern
  passive_magnitude: ->
    [10..15]

# Schutzbrecher
class window.Guardbreaker extends Railpattern
  chance_float: ->
    if @active_pattern == null
      [0, 100]
    else
      if @active_pattern.cost >= 500 then 100.0 else 0.0
  
  passive_magnitude: ->
    1

class window.Healingtwine extends Railpattern
  constructor: (args...) ->
    super
    @concerning_abilities.push 17
  
  chance_float: ->
    100.0
  
  # TODO: Abhängigkeit Selbstheilung
  passive_magnitude: ->
    # (0.1..0.4) * @active_pattern.cost
    [Math.ceil(0.1 * @active_pattern.cost)..Math.ceil(0.4 * @active_pattern.cost)]

# Wissensblitz
class window.Knowdledgeflash extends Railpattern
  chance_float: ->
    100.0
  
  passive_magnitude: ->
    Math.ceil(0.04 * @active_pattern.cost)

# Lebensschnitt
class window.Lifeincision extends Railpattern
  chance_float: ->
    100.0
  
  passive_magnitude: ->
    if @active_pattern instanceof Lifewedge then 20 else 10

# Lebenssicht
class window.Lifesight extends Railpattern
  # 100%
  chance_float: ->
    100.0
  
  # geistlose Lebenssicht
  passive_magnitude: ->
    1

# Lebenskeil
class window.Lifewedge extends Railpattern
  chance_float: ->
    100.0

# Phasensog
class window.Phasepull extends Railpattern
  chance_float: ->
    100.0
  
  passive_magnitude: ->
    1

# Phasenriss
class window.Phaserapture extends Railpattern
  ability_factor_formular: "1.0075 ^ stage"
  
  constructor: (args...) ->
    super
    @concerning_abilities.push 32
  
  ability_factor: ->
    @calc_formular @get_ability(32)
    
  passive_magnitude: ->
    1

# Phasenstoß
class window.Phaserecoil extends Railpattern
  passive_magnitude: ->
    1

# Seelenlicht
class window.Spiritlite extends Railpattern
  # override chara factor
  ability_factor_formular: "1.015 ^ stage"
  
  constructor: (args...) ->
    super
    @concerning_abilities.push 27
  
  ability_factor: ->
    @calc_formular @get_ability(27)
    
  passive_magnitude: ->
    1
  
# parse power function to mathml
mathml_pow = (string, stage) ->
  string.replace /([^\^]+)\s*\^\s*([^\^]+)/, "<msup><mn>$1</mn><mn>" + stage + "</mn></msup>"
  
$(document).ready ->
  # init abilties
  abilities = abilities_as_json
  
  # init railpattern object
  railpatterns = []
  
  railpatterns = (new window[pattern.type](pattern) for pattern in railpatterns_as_json)
  
  $("#abilities tr.ability td.max_stage").click ->
    $("td.stage input", $(this).parents("tr")).val ~~$(this).text()
  
  # calc
  $("#railpattern_configuration button").click ->
    $form = $("#railpattern_configuration")
    
    # update stage
    $("#abilities tr.ability td.stage input").each ->
      ability = (ability for ability in abilities when ~~ability.id is ~~$(this).parents("tr").data('id'))[0]
      ability.stage = ~~$(this).val() unless ability is undefined
    
    # get active pattern
    active_pattern = (railpattern for railpattern in railpatterns when railpattern.name is $("#railpattern_configuration_active_pattern", $form).val())[0]
    return true if active_pattern is undefined
    
    for railpattern in railpatterns
      # set pattern and abilities
      railpattern.active_pattern = active_pattern
      railpattern.set_abilities abilities
      
      # corresponding table row
      $tr = $("#passive_patterns tr.railpattern[data-id='" + railpattern.id + "']")
      
      # update cells
      i18n_key = "tools.railpatterns.passive_effects.r_" + railpattern.id
      magnitude = railpattern.passive_effect()
      if magnitude instanceof Array
        effect = t i18n_key + ".other", 
          min: Math.min.apply Math, magnitude
          max: Math.max.apply Math, magnitude
      else
        effect = t i18n_key,
          count: magnitude
      
      $("td.passive_effect", $tr).text effect
      $("td.chance var", $tr).text railpattern.chance()
      $("td.passive_factor var", $tr).text railpattern.passive_factor()
      $("td.ability_factor var", $tr).text railpattern.ability_factor()
      
    # prevendDefault
    false