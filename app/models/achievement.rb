class Achievement < ActiveRecord::Base  
  alias_attribute :group, :achievement_id
  alias_attribute :achievement_group, :achievement_id
  alias_attribute :id, :achievement_id
  self.primary_keys = :achievement_id, :stage
  
  has_many :progresses, -> { where(:deleted => false) },
           :class_name => 'UsersAchievements',
           :foreign_key => :achievement_id,
           :primary_key => :achievement_id
  has_many :users_achievements, -> { where(:deleted => false) },
           :class_name => 'UsersAchievements',
           :foreign_key => [:achievement_id, :stage]
  has_many :users, :through => :users_achievements
  
  validates :achievement_id, :numericality => {:greater_than => 0}
  validates :created_at, :presence => true
  validates :max_stage, :numericality => {:greater_than => 0}
  validates :name, :presence => true
  
  def self.base_stage
    where(:stage => 1)
  end
  
  def self.including_achiev_count(options = {})
    including_achiev_count = self.select("achievements.*, COUNT(*) as achiev_count")
        .joins("LEFT JOIN users_achievements ON "+
               "(achievements.achievement_id = users_achievements.achievement_id)")
        .where("users_achievements.stage >= achievements.stage")
        .group("achievements.achievement_id")
    including_achiev_count = including_achiev_count.where("users_achievements.world_id IN (?)", options[:in_worlds].collect(&:id)) if options[:in_worlds]
    including_achiev_count
  end
  
  # deprecated
  def achieved(options = {})
    achieved = self.progresses.where("stage >= ?", self.stage)
    achieved = achieved.where(:world_id => options[:in_worlds]) if options[:in_worlds]
    achieved 
  end
  
  # deprecated
  def achiev_count(options = {})
    return self['achiev_count'] if self['achiev_count']
    self.achieved(options).count
  end
  
  def closest
    self.progresses.where("stage < ?", self.stage).order("progress desc")
  end
  
  def furthest
    UsersAchievements.from("(#{self.progresses.where("stage >= ?", self.stage).order("progress desc").to_sql}) #{UsersAchievements.table_name}").group(:achievement_id)
  end
  
  def gfx_file
    if self.gfx.empty?
      "achiev#{self.achievement_id}x#{stage}.gif"
    else
      "#{self.gfx}.gif"
    end
  end
  
  def gfx?
    true
  end
  
  def group_name
    self['name']
  end
  
  def name
    "#{self['name']} (Rang #{self['stage']} von #{self['max_stage']})"
  end
  
  def self.unachieved(options = {})
    if options[:in_worlds]
      world_query = "world_id IN (#{options[:in_worlds].map { |w| w.id.to_i}.join(',')})"
    else 
      world_query = "1"
    end
    joins("RIGHT JOIN (SELECT achievement_id, MAX(stage) + 1 as stage FROM users_achievements WHERE #{world_query} GROUP BY achievement_id) unachieved "\
          "ON #{self.table_name}.achievement_id = unachieved.achievement_id "\
          "AND #{self.table_name}.stage = unachieved.stage")
    .where(Achievement.arel_table[:achievement_id].not_eq(nil))
  end
  
  def self.worlds
    World.where(:language_id => 1)
  end
end
