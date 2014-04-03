class User < ActiveRecord::Base
  include UserNaming
  include UserUrls
  self.primary_keys = :user_id, :world_id
  
  after_initialize :build_world
  
  alias_attribute :updated_at, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :race
  belongs_to :world
  
  has_one :achievement_cache, -> { where(:deleted => false) },
                              :class_name => 'UsersAchievementsCache',
                              :foreign_key => [:user_id, :world_id]
  has_one :registration, -> { order('users_news.created_at DESC') },
                         :class_name => 'UsersNew',
                         :foreign_key => [:user_id, :world_id]
  
  has_many :achievements, :through => :progresses
  has_many :clan_changes, -> { where(:deleted => false) },
                          :class_name => 'UsersClanChange',
                          :foreign_key =>[:user_id, :world_id]
  has_many :experience_changes, -> { where(:deleted => false) },
                                :class_name => 'UsersExperienceChange',
                                :foreign_key =>[:user_id, :world_id]
  has_many :name_changes, -> { where(:deleted => false) },
                          :class_name => 'UsersNameChange',
                          :foreign_key =>[:user_id, :world_id]   
  has_many :race_changes, -> { where(:deleted => false) },
                          :class_name => 'UsersRaceChange',
                          :foreign_key =>[:user_id, :world_id]    
  has_many :progresses, -> { where(:deleted => false) },
                        :class_name => 'UsersAchievements',
                        :foreign_key => [:user_id, :world_id]
  
  scope :in_clans, -> { where("clan_id != 0") }
  
  def build_world
    self.world = World.new if self.world.nil?
  end
  
  def self.last_update
    self.first.updated_at
  end
  
  # to_param override
  def to_param
    [self.user_id, self.world_id].join('-')
  end
  
  def self.primary_from_param(param)
    primaries = param.split('-')
    {:user_id => primaries[0].to_i, :world_id => primaries[1].to_i}
  end
  
  def self.find_by_name_and_world_short(name, world_short)
    joins(:world).where(name: name, worlds: {short: world_short}).take
  end
  
  # composite primary finder method
  def self.from_params(user_params)
    primaries = []
    user_params.map{ |p| User.primary_from_param(p) }.each do |param|
      primaries << "(#{self.table_name}.user_id = #{param[:user_id].to_i} " +
                    "AND #{self.table_name}.world_id  = #{param[:world_id].to_i})"
    end
    
    if primaries.empty?
      return []
    end
    where(primaries.join(' OR '))
  end
  
  def clanstate
    unless self.clan.nil?
      return "leader" if self.clan.leader_id == self.user_id
      return "coleader" if self.clan.coleader_id == self.user_id
      "member"
    else
      "noclan"
    end
  end
end
