class ToolsController < ApplicationController
  def hunt
    @javascripts << 'hunt'
    @stylesheets << 'hunt'
    
    @pos = {x: -336, y: -719}
    @radius = {x: 2, y: 2}
    @range = {
      x: (@pos[:x] - @radius[:x])..(@pos[:x] + @radius[:x]), 
      y: (@pos[:y] - @radius[:y])..(@pos[:y] + @radius[:y])
    }
    
    # npcs in range
    @npcs = Npc.where(pos_x: @range[:x], pos_y: @range[:y])
    @npcs = []
    
    # actual places
    @db_places = Place.where(pos_x: @range[:x], pos_y: @range[:y]).index_by { |place| "#{place.pos_x}#{place.pos_y}" }
    
    @places = []
    @range[:y].each do |y|
      @range[:x].each do |x|
        if @db_places["#{x}#{y}"]
          @places << @db_places["#{x}#{y}"]
        else
          @places << Place.new(pos_x: x, pos_y: y, gfx: "black.jpg")
        end
      end
    end
  end
  
  def railpatterns
    @javascripts << "lib/Railpattern"
    @javascripts << 'railpatterns'
    
    configuration = railpattern_config_params
    
    # abilities that are used for certain patterns
    @abilities = Railpattern.abilities.order(:name)
    
    # merge params with @abilities
    logger.debug configuration[:abilities]
    @abilities.each do |ability|
      logger.debug configuration[:abilities][ability.id.to_s]
      ability.stage = configuration[:abilities][ability.id.to_s].try(:[], "stage")
    end unless configuration[:abilities].blank?
   
    # all the patterns available
    @railpatterns = Railpattern.for_tools.order(:name)
    
    @active_pattern = @railpatterns.where(name: configuration[:active_pattern] || params[:active_pattern]).first
    
    if @active_pattern.nil?
      raise ActiveRecord::RecordNotFound
    end
    
    @railpatterns.map do |railpattern|
      # init abilities
      railpattern.abilities = @abilities
      # set active_pattern
      railpattern.active_pattern = @active_pattern
    end
  end
  
  def ability_calc
    @javascripts << "ability_calc"
    
    @params = ability_calc_params
    
    # create hash with ids as key and basetime, stages as values
    @abilities = Ability.for_calc.inject({}) do |abilities, ability|
      abilities.merge ability.id => new_ability_for_calc(ability)
    end
    
    # abilities submitted
    unless @params[:ability_ids].blank?
      @params[:ability_ids].each_with_index do |id,k|  
        # new ability!
        @abilities[id] ||= new_ability_for_calc(Ability.new(id: id))
        
        @abilities[id][:klass].basetime = @params[:basetime][k]
        @abilities[id][:from_stage] = @params[:from_stage][k]
        @abilities[id][:till_stage] = @params[:till_stage][k]
        
        # shift from stage to till stages to see how much time was already spent
        unless params[:time_spent_mode].nil?
          @abilities[id][:till_stage] = @abilities[id][:from_stage]
          @abilities[id][:from_stage] = 0
        end
      end
    end
    
    # add empty ability
    unless params[:new_ability].nil?
      new_id = Ability.new_id(nil, @abilities)
      @abilities[new_id] = new_ability_for_calc(Ability.new(id: new_id))
    end
    
    # text ability menu submitted => parse abilities
    @params[:abilities_ingame].scan(/([äöüÄÖÜ\w\-]+)\t(\d+)(\t(\d+))?/) do |match|
      id = Ability.new_id(Ability.get_ability_by(:name, match[0], @abilities.collect{ |_, a| a[:klass] }).try(:id), @abilities)
      
      # new ability!
      @abilities[id] ||= new_ability_for_calc(Ability.new(id: id))
      
      # fill in values
      @abilities[id][:klass].name = match[0]
      @abilities[id][:from_stage] = match[1].to_i
      @abilities[id][:till_stage] = match[3].nil? ? @abilities[id][:from_stage] : match[3].to_i
    end
    
    # calculate learntimes
    @abilities.map do |_, a|
      a[:learntimes] = [                                         # lt
        a[:klass].learntime_till(a[:from_stage], a[:till_stage], @abilities[3][:till_stage]),
        a[:klass].learntime_till(a[:from_stage], a[:till_stage], @abilities[3][:from_stage])
      ]
    end
    
    # calc sum times
    @learntime_sum = [
      @abilities.collect { |_, a| a[:learntimes].max }.sum,
      @abilities.collect { |_, a| a[:learntimes].min }.sum
    ]
  end
  
  private
  
  def ability_calc_params
    strong_params = params.permit(:abilities_ingame, ability_ids: [], basetime: [], from_stage: [], till_stage: [])
    
    [:ability_ids, :basetime, :from_stage, :till_stage].each do |key|
      strong_params[key] ||= []
      strong_params[key].map!(&:to_i)
    end
    
    strong_params[:abilities_ingame] ||= ""
    
    strong_params
  end
  
  def new_ability_for_calc(ability)
    {
      klass: ability,
      from_stage: 0,
      till_stage: 0,
      learntime_min: 0,
      learntime_max: 0
    }
  end
  
  def railpattern_config_params
    params[:railpattern_configuration] || {
      :active_pattern => nil,
      :abilities => []
    }
  end
  
end