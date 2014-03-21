class Admin::AchievementsController < Admin::BaseController
  after_filter :only => [:create, :update, :destroy] do
    #expires_action(:controller => '/achievements') if @save
  end
  
  # GET /admin/achievements
  # GET /admin/achievements.json
  def index
    @achievements = Achievement.order([:name, :stage])
    
    respond_to do |format|
      format.html # index.html.erb
      format.json { render json: @admin_achievements }
    end
  end

  # GET /admin/achievements/1
  # GET /admin/achievements/1.json
  def show
    @admin_achievement = Achievement.find(params[:id])

    respond_to do |format|
      format.html # show.html.erb
      format.json { render json: @admin_achievement }
    end
  end

  # GET /admin/achievements/new
  # GET /admin/achievements/new.json
  def new
    @admin_achievement = Achievement.new

    respond_to do |format|
      format.html # new.html.erb
      format.json { render json: @admin_achievement }
    end
  end

  # GET /admin/achievements/1/edit
  def edit
    @admin_achievement = Achievement.find(params[:id])
  end

  # POST /admin/achievements
  # POST /admin/achievements.json
  def create    
    1.upto(params[:achievement][:max_stage].to_i) do |stage|
      name = params[:achievement][:name]
      
      attributes = {
        :created_at => Date.parse(params[:achievement][:created_at]),
        :max_stage => params[:achievement][:max_stage],
        :name => name
      }
      
      unless params[:achievement][:gfx].blank?
        attributes[:gfx] = "#{params[:achievement][:gfx]}#{stage}"
      end
      
      @admin_achievement = Achievement.find_or_create_by_stage_and_achievement_group(stage, params[:achievement][:achievement_group])
      @admin_achievement.update_attributes(attributes)
      
      @save = @admin_achievement.save
    end

    respond_to do |format|
      if @save
        generate_dump(Achievement)
        format.html { redirect_to [:admin,@admin_achievement], notice: 'Achievement was successfully created.' }
        format.json { render json: @admin_achievement, status: :created, location: @admin_achievement }
      else
        format.html { render action: "new" }
        format.json { render json: @admin_achievement.errors, status: :unprocessable_entity }
      end
    end
  end

  # PUT /admin/achievements/1
  # PUT /admin/achievements/1.json
  def update
    @admin_achievement = Achievement.find(params[:id])
    @save = @admin_achievement.update_attributes(achievement_params)

    respond_to do |format|
      if @save
        generate_dump(Achievement)
        format.html { redirect_to [:admin, @admin_achievement], notice: 'Achievement was successfully updated.' }
        format.json { head :ok }
      else
        format.html { render action: "edit" }
        format.json { render json: @admin_achievement.errors, status: :unprocessable_entity }
      end
    end
  end

  # DELETE /admin/achievements/1
  # DELETE /admin/achievements/1.json
  def destroy
    @admin_achievement = Achievement.find(params[:id])
    @save = @admin_achievement.destroy

    respond_to do |format|
      format.html { redirect_to admin_achievements_url }
      format.json { head :ok }
    end
  end
  
  private 
  
  def achievement_params
    params.require(:achievement).permit(:achievement_group, :stage, :name, 
                                        :description, :max_stage, :needed, 
                                        :reward, :gfx)
  end
end
