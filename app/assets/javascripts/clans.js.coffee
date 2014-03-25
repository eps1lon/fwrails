$(document).ready ->
  $('.suggest #name').autocomplete({
    source: (request, response) -> 
      url = clans_url.replace /_name_/, request.term
      clans = []
      
      $.getJSON url, (data) ->
        #console.log data
        for clan in data
          world = (world for world in worlds when world.id is clan.world_id)[0]['short']
          clan.name = clan.tag if clan.name is undefined
          clans.push {value: clan.name, label: clan.name + '(' + world + ')', world: world, clan_id: clan.clan_id}
        response clans
    focus: (event, ui) ->
      $(event.toElement).prop 'href', (clan_url.replace /_id_/, ui.item.clan_id).replace /_world_/, ui.item.world
  })