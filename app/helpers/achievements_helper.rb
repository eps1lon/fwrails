module AchievementsHelper
  def cache_key_for_achievements(action, worlds)
    last_update = UsersAchievementsCache.last_update.try(:utc).try(:to_s, :number)
    world_cache_key = relation_to_cache_key(worlds)
    ["statistics", action, last_update] + world_cache_key
  end
  
  def achievement_url(achievement, options = {})
    options[:group_page] ||= false
    return super(achievement.group_name) if options[:group_page]
    super(achievement.group_name, achievement.stage)
  end
  
  def achievements_rank_add_id_url(id)
    new_params = params.clone
    new_params[:ids] ||= ''
    new_params[:ids] = new_params[:ids].split(',').push(id).join(',')

    achievements_rank_url(new_params)
  end
end
