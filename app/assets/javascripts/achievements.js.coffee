# Place all the behaviors and hooks related to the matching controller here.
# All this logic will automatically be available in application.js.
# You can use CoffeeScript in this file: http://jashkenas.github.com/coffee-script/

toggle_duration = "slow"

ids_ = -> 
  ids = []
  $('#users th[data-container-for]').each ->
    unless $(this).css('display') == 'none'
      ids.push +$(this).data('container-for').replace(class_name_(''), '')
  ids

ids_changed = ->
  href = achievements_rank_url.replace(/_ids_/, ids_().join(','))
  $('#permalink a').attr('href', href).html(href)

add_ids_to_href = (event, selector) ->
  that = $(selector || this)
    
  # get display groups
  ids = ids_()
  
  # and insert these groups into the sort href
  if ids.length # groups displayed
    that.attr('href', that.attr('href').replace('_ids_', ids.join(',')))
  else
    that.attr('href', that.attr('href').replace('/show/_ids_', ''))

class_name_ = (col) ->
  'achiev_group_' + col

create_col = (from, after) ->
  group = group_ from
  class_name = class_name_ group
  attr = 'container-for="'  + from.attr('id') + '"'
  
  if $('th[' + attr + ']').length == 0
    $('<th ' + attr + '></th>').append(from).insertAfter(after)
    
    $('tbody tr').each (i, row) ->
      $('<td/>', {
        class: class_name,
        html: '-'
      }).insertAfter($(after.selector, row))
    
    return true # successfully created
    
  false # already created
  
fill_col = (col, values) ->
  class_name = class_name_ col
  col = +col
  
  $('tbody tr').each -> # each user_row
    user_primary = this.id.split('_')[1] # get user_primary_id
    
    $('td.'+class_name, this).text(values[user_primary] || 0)
    
group_ = (container) ->
  +container.attr('id').replace class_name_(''), ''

show_achiev_group = ($item, shown) ->
  #$item.hide toggle_duration
  after = '.user_id'
  attr = 'data-container-for="' + $item.attr('id') + '"'
  group = group_ $item
  class_name = class_name_ group
  
  $th = $('#users th[' + attr + ']')
  create = $th.length == 0

  if create
    $th = $('<th ' + attr + ' data-order="' + group + '" style="display: none"></th>').
          insertAfter($(after, $('#users thead'))).show(toggle_duration)
    $sort = $('<a href="' + fallback_urls['#users th a'].href.replace('_order_', group) + '" class="sort no-content"></a>')
    $sort.click add_ids_to_href
    $th.append $sort
          
    users = []
    # get displayed users
    $('#users tbody tr').each (i, row) ->
      users.push row.id.split('_')[1]
      $('<td class="' + class_name + '" style="display: none">-</td>').insertAfter($(after, row)).show toggle_duration

    url = achievements_group_progress_url.replace(/_users_/, users.join(',')).
                                          replace(/_group_/, group_($item))

    values = {}

    $.getJSON url, (data) ->
      # parse data into correct format
      # {user_primary: value}
      $.each data, (i, row) ->
        values[row.user_id + '-' + row.world_id] = row.stage

      fill_col group, values  
   else
    $th.show toggle_duration
    $('#users tbody td.' + class_name).show toggle_duration

  $item.prependTo($th).fadeIn()
  
  ids_changed()
    
hide_achiev_group = ($item, shown) ->
  $('#users tbody td.' + $item.attr('id')).hide toggle_duration
  $item.parent('th').hide toggle_duration
  #$item.hide toggle_duration, ->
  attr = 'data-container-for="' + $item.attr('id') + '"'
  $li = $('#achiev_groups li[' + attr + ']')
  
  if $li.length == 0
    $li = $('<li ' + attr + '></li>').appendTo $('#achiev_groups')

  $item.appendTo($li).show toggle_duration
  
  ids_changed()

$(document).ready ->  
  # rank drag drop
  $('.ui-draggable').draggable {
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
  fallback_urls = fallback_urls || {}
  $.each fallback_urls, (selector, link) ->
    $(selector).each ->
      that = $(this)
      that.attr('href', link.href.replace('_' + link.replace + '_', that.parent().data(link.replace)))
  $('nav.users a').each ->
      that = $(this)
      that.attr('href', users_nav_url.replace('_page_', that.text()))

  # append added/removed achievements to these links
  $('#users th a, nav.users a, ul.worlds a').click add_ids_to_href
  
  # title fallback
  $('.ui-draggable[id*="achiev_group_"]').tooltip {
    content: ->
      return $(this).attr 'title'
    track: true
  }