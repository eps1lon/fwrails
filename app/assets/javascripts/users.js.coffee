$(document).ready ->
  $('#achievements li.achievement').mouseover ->
    achievement_id = $(this).data('achievement_id')
    
    users_achievement = (achievement for achievement in user.achievements when achievement.achievement_id is achievement_id)[0]
    
    $('#achievements_progress').html l("number", users_achievement.progress)
    
    if users_achievement.achievement
      $('#achievements_caption').html users_achievement.achievement.name
      $('#achievements_description').html users_achievement.achievement.description
      $('#achievements_reward').html l("number", users_achievement.achievement.reward)
    
    $('#achievements_data').show 0
  
  $('.suggest #name').autocomplete({
    source: (request, response) -> 
      url = users_url.replace /_name_/, request.term
      users = []
      set_loading true
      
      $.getJSON url, (data) ->
        #console.log data
        for user in data
            world = (world for world in worlds when world.id is user.world_id)[0]['short']
            users.push {value: user.name, label: user.name_primary, world: world}
          response users
        set_loading false
    focus: (event, ui) ->
      $(event.toElement).prop 'href', (user_url.replace /_name_/, ui.item.value).replace /_world_/, ui.item.world
  })