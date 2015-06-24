class window.Ability
  constructor: (@id, @basetime) ->
  
  learntime: (stage, lt = 0) ->
    ~~(@basetime * @.lt_factor(lt) * (stage - 1))
    
  learntime_till: (from_stage, till_stage, lt = 0) ->
    if from_stage > till_stage || till_stage == 1
      0 
    else
      #                                       gaussian sum
      ~~(@basetime * @.lt_factor(lt) * 
      # gaussian: sum_(i=1)^n = (n^2 + n) / 2
      # sum_(i=from_stage+1)^till_stage i = sum_(i=1)^till_stage - sum_(i=1)^(from_stage+1) 
      (((till_stage - 1) ** 2 + till_stage - (from_stage - 1) ** 2 - from_stage) / 2))
  
  lt_factor: (stage) ->
    # lerntechnik hat keinen einfluss auf sich selbst
    if @id == 3 then 1 else @constructor.lt_factor(stage)
  
  @lt_factor: (stage) ->
    0.99 ** stage