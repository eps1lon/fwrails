toggle_duration = 0

ids_ = -> 
  ids = []
  $('th .ui-draggable:not(.ui-draggable-dragging)').each ->
    ids.push(+$(this).attr('id').replace id_(''), '')
  ids

ids_changed = ->
  $link = $('#permalink a')
  ids = ids_()
  if ids.length
    href = $link.data('template').replace('_ids_', ids_().join(','))
  else
    href = $link.data('template').replace('/show/_ids_', '')
  $link.attr('href', href).html(href)

add_ids_to_href = (event, selector) ->
  $that = $(selector || this)
    
  # get display groups
  ids = ids_()
  
  # and insert these groups into the sort href
  if ids.length # groups displayed
    $that.attr('href', $that.attr('href').replace('_ids_', ids.join(',')))
  else
    $that.attr('href', $that.attr('href').replace(/\/show\/[^\/]*/, ''))

override_fallback_url = (event) ->
  $that = $(this)

  # custom dynamic link data
  fallback_key = $that.parents('*[data-fallback_url]').data('fallback_url')
  link = fallback_urls[fallback_key]
  $that.attr('href', link.href.replace('_' + link.replace + '_', $that.data(link.replace)))
  #console.log $that.attr('href')

  # add displayed achievements
  add_ids_to_href null, this

  #console.log $that.attr('href')
  true

id_ = (col) ->
  'achiev_group_' + col

create_col = ($from, after) ->
  group = group_ $from
  attr = 'data-container-for="'  + $from.attr('id') + '"'

  $th = $('th[' + attr + ']')
  create = $th.length == 0
  
  if create
    th_real = $('<th ' + attr + ' colspan="2"></th>').css('display', 'none').insertAfter('th' + after)
    $th = $('<div class="position_on_table_element"></div>').appendTo(th_real)

    $sort = $('<a data-order="' + group + '" class="sort no-content"></a>')
    $sort.click override_fallback_url
    $th.append $sort
    
    $('tbody tr').each (i, row) ->
      $td = $('<td/>', {
        class: 'achiev_group'
        'data-achievement_id': group
        html: '-'
      }).data('achievement_id', group).css('display', 'none')

      $td.clone().addClass('stage').insertAfter($(after, row))
      $td.clone().addClass('progress').insertAfter($(after, row))
  
  $th.append($from)
  create
  
fill_col = (col, values) ->
  col = +col
  
  $('tbody tr').each -> # each user_row
    user_primary = $(this).data 'user' # get user_primary_id
    users_achievement = values[user_primary] || {progress: '-', stage: '-'}

    $('td.achiev_group.progress[data-achievement_id="' + col + '"]', this).text(users_achievement.progress || 0)
    $('td.achiev_group.stage[data-achievement_id="' + col + '"]', this).text(users_achievement.stage || 0)
    
group_ = (container) ->
  +container.attr('id').replace id_(''), ''

show_achiev_group = ($item, shown) ->
  #$item.hide toggle_duration
  after = '.user_id'
  attr = 'data-container-for="' + $item.attr('id') + '"'
  group = group_ $item

  create = create_col $item, '.user_id'
  if create
    users = []
    # get displayed users
    $('#users tbody tr.user').each (i, row) ->
      users.push $(row).data('user')

    url = achievements_group_progress_url.replace(/_users_/, users.join(',')).
                                          replace(/_group_/, group)

    $.getJSON url, (data) ->
      values = {}
      # parse data into correct format
      # {user_primary: value}
      $.each data, (i, row) ->
        values[row.user_id + '-' + row.world_id] = {progress: row.progress, stage: row.stage}
      
      fill_col group, values  
  
  $('th[data-container-for="' + $item.attr('id') + '"]').show toggle_duration
  $('.achiev_group[data-achievement_id="' + group + '"]').show toggle_duration
  
  ids_changed()
    
hide_achiev_group = ($item, shown) ->
  $('#users tbody td.achiev_group[data-achievement_id="' + group_($item) + '"]').hide toggle_duration
  $item.parents('th').hide toggle_duration
  #$item.hide toggle_duration, ->
  attr = 'data-container-for="' + $item.attr('id') + '"'
  $li = $('#achiev_groups li[' + attr + ']')
  
  if $li.length == 0
    $li = $('<li ' + attr + '></li>').appendTo $('#achiev_groups')

  $item.appendTo($li).show toggle_duration
  
  ids_changed()

$(document).ready ->  
  # rank drag drop
  $('img.ui-draggable').draggable {
    cursor: "move"
    helper: "clone"
    revert: "invalid"
    zIndex: 999
  }

  $('#users thead tr').droppable {
    tolerance: "pointer"
    drop: (event, ui) ->
      show_achiev_group ui.draggable
  }
  
  $("#achiev_groups").droppable {
    drop: (event, ui) ->
      hide_achiev_group ui.draggable
  }
  
  # overwrite no-js fallback
  $('*[data-fallback_url] a').click override_fallback_url
     
  # title fallback
  $('.ui-draggable').tooltip {
    content: ->
      return $(this).attr 'title'
    track: true
  }