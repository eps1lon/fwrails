class AreasController < ApplicationController
  def index
    @areas = Area.order(:name)
  end
  
  def details
    @area = Area.where(:name => params[:name]).first
    
    @npcs = []
    @area.places.each do |place|
      @npcs += place.npcs
    end
  end
  
  def places
    @area = Area.where(:name => params[:name]).first
    
    @places = @area.places
  end
end
