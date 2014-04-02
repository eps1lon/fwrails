build_url = (record, type) ->
  $('<a/>', {
    href: url_for(spotlight_urls[type] || '', record)
    html: record.name_primary
  })
  
build_message = (type, i18n_key, count) ->
  $('<em/>', {
    html: t("spotlights." + type + ".message", 
            {
              type: t("spotlights." + type + ".types." + i18n_key),
              count: l("number", count)
            }
           )
  })

$(document).ready ->
  $('#spotlight').draggable {
    cursor: "move"
    zIndex: 999
  }
  
  $('#spotlight h1 + a').click ->
    $('#spotlight').hide()
    false
  
  $.getJSON spotlight_urls.spotlights, (json) ->
    for type, spotlight of json
      $('p.spotlight.' + type).append build_url(spotlight[type], type), 
                                      build_message(type, spotlight.i18n_key, spotlight.num)
                                                 
    set_loading false, "#loading_spotlights"