class SpotlightsController < ApplicationController
  @@refresh_every = 30.minutes
  
  def show
    @spotlights = Spotlight.new(@@refresh_every)
    #spotlights.map(&:to_sym).each do |spotlight|
    #  @spotlights[spotlight] = Spotlight.new(@@refresh_every).send(spotlight)
    #end
    
    respond_to do |format|
      format.json { render json: @spotlights }
    end
  end
end
