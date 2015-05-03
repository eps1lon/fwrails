class NpcsController < ApplicationController
  def index
    @npcs = Npc.persistent_npcs.order(:id)
  end

  def show
  end
  
  def api
    # verfügbare Parameter für view
    @api_parameters = %w{id name x x_range y y_range unique_npc}
    
    # formats for view
    @formats = %w{html csv json xml}
    
    # limiter for multiple names/ids etc.
    @delimiter = "|"
    
    all_npcs = Npc.persistent_npcs.order(:id)
    @npcs = all_npcs
    
    # position limitation
    [:x, :y].each do |pos|
      if params[pos]
        pos_value = params[pos].to_i
        range = params["#{pos}_range"].to_i
        @npcs = @npcs.where(("pos_#{pos}").to_sym => (pos_value - range)..(pos_value + range))
      end
    end
    
    # ids
    if params[:id]
      @npcs = @npcs.where(id: params[:id].split(@delimiter))
    end
    
    # names
    if params[:name]
      @npcs = @npcs.where(name: params[:name].split(@delimiter))
    end
    
    # unique npc
    if params[:unique_npc]
      @npcs = @npcs.where(unique_npc: params[:unique_npc])
    end
    
    respond_to do |format|
      format.html
      format.any(:xml, :csv, :json) { render request.format.to_sym => @npcs, except: %w{updated_at created_at description}}
    end
  end
end
