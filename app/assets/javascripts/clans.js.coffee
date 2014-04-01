$(document).ready ->
  $('.suggest #name').autocomplete({
    source: (request, response) -> 
      url = clans_url.replace /_name_/, request.term
      clans = []
      set_loading true
      
      $.getJSON url, (data) ->
        #console.log data
        for clan in data
          world = (world for world in worlds when world.id is clan.world_id)[0]['short']
          clans.push {value: clan.clan_id, label: clan.name_primary, world: world}
        response clans
        set_loading false
    focus: (event, ui) ->
      $(event.toElement).prop 'href', (clan_url.replace /_id_/, ui.item.value).replace /_world_/, ui.item.world
  })