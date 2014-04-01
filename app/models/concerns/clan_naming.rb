module ClanNaming
  extend ActiveSupport::Concern
  
  def name_primary
    name = self.try(:name) || self.try(:tag) || self.clan.try(:name) || "Clan `#{self.clan_id}`"
    "#{name} (#{world.short})"
  end
end