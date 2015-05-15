class MapController < ApplicationController
  def index
    # max radius
    @max_radius = 7
    
    # strong params
    @params = map_params
    
    # tile width/height in px
    @tile_size = 50
    
    # get places and draw border
    @places = Place.invert(:houses)
                   .minimap(@params[:x], @params[:y], @params[:radius] + 1)
                   .includes(:npcs)
    @places_with_border = draw_border @places
    
    # npcs
    @npcs = Npc.in_range(@params[:x], @params[:y], @params[:radius]).order(name: :asc)
    
    # dimensions
    @map_dimensions = map_dimensions(@places_with_border)
    
    # world for path
    @image_world = World.image_world.take
  end

  def show
  end
  
  private
  
  def draw_border(places)
    # execute query
    places = places.to_a
    
    # get boundary
    map_size = map_dimensions(places)
    min_x = map_size[:min_x]
    max_x = map_size[:max_x]
    min_y = map_size[:min_y]
    max_y = map_size[:max_y]
    
    # walk each place
    (min_x..max_x).each do |x|
      (min_y..max_y).each do |y|
        # place already exists here
        next unless places.select { |place| place.pos_x == x && place.pos_y == y}.empty?
        
        # and check if a surrounding place is an actual place
        ((x-1)..(x+1)).each do |edge_x|
          ((y-1)..(y+1)).each do |edge_y|
            # not the current place
            next if edge_x == x && edge_y = y

            place_edges = places.select { |place| place.pos_x == edge_x && place.pos_y == edge_y}
            place_edge = place_edges.first

            # surrounding place exists and is not already a border place
            if !place_edge.nil? && !place_edge.is_border_place?
              places << Place.border_place(x, y)
            end
          end
        end
      end
    end
    
    # return
    places
  end
  
  def map_dimensions(places)
    boundaries = {
      min_x: places.min_by { |p| p.pos_x }.pos_x,
      max_x: places.max_by { |p| p.pos_x }.pos_x,
      min_y: places.min_by { |p| p.pos_y }.pos_y,
      max_y: places.max_by { |p| p.pos_y }.pos_y
    }
    
    boundaries.merge({
      height: boundaries[:max_y] - boundaries[:min_y] + 1,
      width: boundaries[:max_x] - boundaries[:min_x] + 1
    })
  end
  
  def map_params
    # numeric only params
    [:x, :y, :radius].each do |key|
      params[key] = params[key].to_i
    end
    
    # defaults here since we allow them per get in routes match and post via form
    {x: 92, y: 105, radius: 2}.each do |param, default|
      params[param] = default if params[param] == 0
    end
    
    params[:radius] = [1, [@max_radius, params[:radius].abs].min].max
    
    params.permit(:x, :y, :radius)
  end
  
end
