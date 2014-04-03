module AchievementUrls
  extend ActiveSupport::Concern
  
  def link_anchor
    "achiev#{achievement_id}s#{stage}"
  end
end
