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
    
    # all the patterns available
    @railpatterns = Railpattern.for_tools.order(:name)
    
    # abilities that are used for certain patterns
    @abilities = Railpattern.abilities
  end
end