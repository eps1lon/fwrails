//= require jquery
//= require jquery.ui.all
//= require jquery_ujs
//= require i18n
//= require i18n/translations
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