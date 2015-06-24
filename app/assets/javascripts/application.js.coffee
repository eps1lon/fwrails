//= require jquery
//= require jquery.ui.all
//= require jquery_ujs
//= require i18n
//= require i18n/translations
//= require lib/Array
//= require lib/Number
//= require datepicker
//= require spotlights
//= require_self
$(document).ready ->
  # toggle links
  $('[data-toggle-for]').click ->
    t_key = if $('#' + $(this).data('toggle-for')).toggle().css('display') == 'none' then 'show' else 'hide'
    $(this).text t('helpers.toggle.' + t_key);
  
  # selects form value when clicked
  $(document).on "focus", "input.auto_select, textarea.auto_select", ->
    return if window.focused_element == this
    window.focused_element = this
    setTimeout -> 
      window.focused_element.select()
    , 0 # focus doesnt stick in chrome without timeout

$('.races li, .worlds li').tooltip {
  content: ->
    return $(this).attr 'title'
  track: true
}

window.url_for = (url, record) ->
  url.replace /\*([^\*]+)\*/g, (_, key) ->
    value_deep(key, record)

window.set_loading = (state, for_selector) ->
  for_selector ||= '#loading'
  $(for_selector).prop 'checked', state

window.value_deep = (key_deep, obj) ->
  return obj if (typeof obj).toLowerCase() != 'object'
  for key in key_deep.split "."
    break if !obj[key]
    obj = obj[key]
  obj

# i18n-js wrapper
window.l = (type, value) ->
  I18n.l(type, value)
  
window.t = (scope, locals) ->
  I18n.t(scope, locals);
  
# translation of Apps ToolsHelper::countdown
window.countdown = (s) -> 
  units = countdown_units(s)
  (word for word in [
    t("datetime.distance_in_words.x_years",   {count: units.y}),
    t("datetime.distance_in_words.x_days",    {count: units.d}),
    t("datetime.distance_in_words.x_hours",   {count: units.h}),
    t("datetime.distance_in_words.x_minutes", {count: units.m}),
    t("datetime.distance_in_words.x_seconds", {count: units.s})
  ] when !!word).to_sentence({ # filter with word for word in words when !word.empty?
    two_words_connector: t("support.array.connector.and"),
    last_word_connector: t("support.array.connector.and")
  })

# translation of Apps ToolsHelper::countdown_units
countdown_units = (t) ->
  [mm, ss] = t.divmod(60)            
  [hh, mm] = mm.divmod(60)          
  [dd, hh] = hh.divmod(24)  
  [yy, dd] = dd.divmod(365)
  
  {
    y: yy,
    d: dd, 
    h: hh, 
    m: mm, 
    s: ss
  }