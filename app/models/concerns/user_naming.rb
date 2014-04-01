module UserNaming
  extend ActiveSupport::Concern
  
  def name_primary
    name = self.try(:name) || self.user.try(:name) || "User `#{self.user_id}`"
    "#{name} (#{world.short})"
  end
end