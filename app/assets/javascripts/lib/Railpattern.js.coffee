class window.Railpattern
  ability: null # id of the ability for chara_factor
  ability_factor_formular: "1"
  constructor: (attributes) ->
    @[key] = value for key, value of attributes
    @concerning_abilities = []
    @active_pattern = null
  
  # method for options_from_collection_for_select
  name_with_cost: ->
    #TODO number_with_delimiter?
    @name + " " + @cost
  
  # actual chance that is used ingame
  chance: ->
    Math.ceil @chance_float()
  
  ability_factor: ->
    @calc_formular 0

  # Chance as float
  chance_float: ->
    100 * @ability_factor() * @active_pattern.active_factor() * @passive_factor()
    
  # active factor for passive chance
  active_factor: ->
    @cost / 10000
  
  # passive factor for chance
  passive_factor: ->
    #0.95 ** (@cost / 125)
    Math.pow(0.95, @cost / 125)
    
  active_magnitude: ->
  
  life_drain: ->
    0
  
  # wrapper function for passive magnitude to supress any result if active_pattern == passive_pattern
  passive_effect: ->
    if @active_pattern instanceof @.constructor then 0 else @passive_magnitude()
    
  # magnitude of the passive effect
  # 0 for no effect
  passive_magnitude: ->
    0
    
  set_abilities: (abilities) ->
    @abilities = (ability for ability in abilities when ability.id in @concerning_abilities)
    
  get_ability: (ability_id) ->
    ability = (ability for ability in @abilities when ability.id is ability_id)[0]
    if ability is undefined then 0 else ability.stage
  
  calc_formular: (stage) ->
    eval(@parse_formular(@ability_factor_formular, stage))

  parse_formular: (string, stage) ->
    string.replace(/([^\^]+)\s*\^\s*([^\^]+)/, "Math.pow($1, $2)").replace("stage", stage)