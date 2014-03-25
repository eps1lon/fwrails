$(document).ready ->
  create_option = (elem, select, selected) ->
    option = $ '<option value="' + elem[1] + '">' + elem[0] + '</option>'
    option.prop 'selected', selected
    select.append option
    
  $('#add_user').click ->
    set_loading true
    
    $.ajax({
      data: $('form#achievement_graph').serialize(),
      type: 'POST',
      error: (e) -> 
        console.log e
      success: (json) ->
        select = $('#users')
        
        # clear select
        select.empty()
        
        create_option user, select, true for user in json.users
        
        # user_name is empty if the user was found
        $('#user_name').val json.user.name 
        set_loading false
    })
    
    # prevent default
    false