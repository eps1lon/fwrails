//= require jquery
//= require jquery.ui.all
//= require jquery_ujs
//= require i18n
//= require i18n/translations
//= require datepicker
//= require_self
$(document).ready ->
  $('[data-toggle-for]').click ->
    t_key = if $('#' + $(this).data('toggle-for')).toggle().css('display') == 'none' then 'show' else 'hide'
    $(this).text t('helpers.toggle.' + t_key);

$('.races li, .worlds li').tooltip {
  content: ->
    return $(this).attr 'title'
  track: true
}

# i18n-js wrapper
window.l = (type, value) ->
  I18n.l(type, value)
  
window.t = (scope, locals) ->
  I18n.t(scope, locals);