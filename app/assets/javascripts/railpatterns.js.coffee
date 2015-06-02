# Seelenlicht
class window.Spiritlite extends Railpattern
  ability: 1
  # override chara factor
  chara_factor_formular: "1.015 ^ stage"

# Phasenriss
class window.Phaserapture extends Railpattern 
  ability: 2
  # override chara factor
  chara_factor_formular: "1.00075 ^ stage"

# parse power function to mathml
mathml_pow = (string, stage) ->
  string.replace /([^\^]+)\s*\^\s*([^\^]+)/, "<msup><mn>$1</mn><mn>" + stage + "</mn></msup>"
  
$(document).ready ->
  # init railpattern object
  railpatterns.map (pattern) ->
    pattern.class = new window[pattern.type](pattern.cost)
  
  # calc
  $("#calc_railpattern_chance").click ->
    # get active_pattern
    active_pattern = pattern for pattern in railpatterns when pattern.id == +$("#active_pattern option:selected").val()
    
    # passive_patterns
    # get ids first
    passive_pattern_ids = ($(pattern).data().id for pattern in  $("#passive_patterns input[type=checkbox]:checked"))
    # and intersect with railpatterns 
    passive_patterns = (pattern for pattern in railpatterns when pattern.id in passive_pattern_ids)

    # get summary list
    $chances = $("#chances")
    
    # clear summary list
    $chances.empty()
    
    # walk each passive pattern an calculate chances
    for pattern in passive_patterns
      # get stage
      stage = +$(ability).val() for ability in $(".ability_stage") when +$(ability).data("id") == pattern.class.ability
      
      # chances
      chance_float = pattern.class.chance_float(active_pattern.class, stage)
      chance = pattern.class.chance(active_pattern.class, stage)
      
      # create list elem
      $li = $("<li><math><mi class='result'>" + pattern.name + "</mi> = " + 
              "<mn class='result'>" + chance + "</mn><mi class='unit'>%</mi> <mo>=</mo> " + 
              "<mo>" + t("tools.railpatterns.formular.ceil") + "</mo>(<mn>" + chance_float + "</mn>) " +
              "<mi>%</mi> <mo>=</mo> <mo>" + t("tools.railpatterns.formular.ceil") + 
              "</mo>(<mn>100</mn> <mo>*</mo> <mn>" +  active_pattern.class.active_factor() + "</mn> " + 
              "<mo>*</mo> <mn>" +  pattern.class.passive_factor() + "</mn> " +
              "<mo>*</mi> <mn>" + pattern.class.chara_factor(stage) + "</mn>) <mi>%</mi>" + 
              "</math></li>")
              
      if pattern.class.ability != null
        $li.append ", <math><mi>" +  t("tools.railpatterns.formular.chara_factor") +
                   "(<mi>" + t("tools.railpatterns.abilities.a_" + pattern.class.ability) + 
                   "</mi>)</mi> <mo>=</mo> <mn class='result'>" + pattern.class.chara_factor(stage) + 
                   "</mn> <mo>=</mo>" + mathml_pow(pattern.class.chara_factor_formular, stage) +
                   "</math>"
              
      # append
      $chances.append $li