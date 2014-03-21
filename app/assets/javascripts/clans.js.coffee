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
          clans.push {value: clan.name, label: clan.name + '(' + world + ')', world: world}
        response clans
    select: (event, ui) ->
      #window.location.href = (clan_url.replace /_name_/, ui.item.value).replace /_world_/, ui.item.world
  })