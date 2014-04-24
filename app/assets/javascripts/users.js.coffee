$(document).ready ->
  $('#achievements li.achievement').mouseover ->
    $this = $(this)
    achievement_id = $this.data('achievement_id')
    
    users_achievement = (achievement for achievement in user.achievements when achievement.achievement_id is achievement_id)[0]
    achievement = users_achievement.achievement || users_achievement.next_stage
    
    $('#achievements_progress').html l("number", $this.data('progress_value')) + " / " + 
                                     l("number", $this.data('progress_max'))
    
    if achievement
      $('#achievements_caption').html achievement.name
      $('#achievements_description').html achievement.description
      $('#achievements_reward').html l("number", achievement.reward)
    
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