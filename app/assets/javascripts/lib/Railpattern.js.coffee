class window.Railpattern
  ability: null # id of the ability for chara_factor
  chara_factor_formular: "1"
  constructor: (@cost) ->
  
  active_factor: ->
    @cost / 10000
  
  passive_factor: ->
    0.95 ** (@cost / 125)
  
  chara_factor: (stage) ->
    eval(this.parse_formular(this.chara_factor_formular))
  
  # Chance as float
  chance_float: (active_pattern, stage) ->
    100 * this.chara_factor(stage) * active_pattern.active_factor() * this.passive_factor()
  
  # actual chance that is used ingame
  chance: (active_pattern, stage) ->
    Math.ceil this.chance_float(active_pattern, stage)
    
  parse_formular: (string) ->
    string.replace /([^\^]+)\s*\^\s*([^\^]+)/, "Math.pow($1, $2)"