class Clan < ActiveRecord::Base
  self.primary_keys = :clan_id, :world_id
  
  TAG_FLAGS = Hash[%w{notag inspected}.each_with_index.map {|flag,i| [flag.to_sym, 2**i]}]
  
  belongs_to :world
  
  alias_attribute :updated_at, :created_at
  
  has_one :leader, :class_name => 'User', 
                   :foreign_key => [:user_id, :world_id], 
                   :primary_key => [:leader_id, :world_id]
  has_one :coleader, :class_name => 'User', 
                     :foreign_key => [:user_id, :world_id], 
                     :primary_key => [:coleader_id, :world_id]
  
  has_many :coleader_changes, :class_name => 'ClansColeaderChange', 
                              :foreign_key => [:clan_id, :world_id]
  has_many :leader_changes, :class_name => 'ClansLeaderChange', 
                            :foreign_key => [:clan_id, :world_id]
  has_many :name_changes, :class_name => 'ClansNameChange', 
                          :foreign_key => [:clan_id, :world_id]
  has_many :tag_changes, :class_name => 'ClansTagChange', 
                         :foreign_key => [:clan_id, :world_id]
  
  has_many :members, :class_name => 'User', :foreign_key => [:clan_id, :world_id]
  has_many :adds, :class_name => 'UsersClanChange', 
                  :foreign_key => [:clan_id_new, :world_id],
                  :primary_key => [:clan_id, :world_id]
  
  has_many :outs, :class_name => 'UsersClanChange', 
                  :foreign_key => [:clan_id_old, :world_id],
                  :primary_key => [:clan_id, :world_id]
  
  def name_primary
    "#{self.tag} (#{self.world.short})"
  end
  
  def tag
    self.tag_printable
  end
  
  def tag_is?(flag)
    TAG_FLAGS.include?(flag) && (self.tag_flags & TAG_FLAGS[flag]).to_b
  end
  
  def tag_flags
    flags = 0
    tag = self['tag']

    if tag.blank? 
      flags |= TAG_FLAGS[:notag]
    else
      if tag.scan(/[[:print:]]/).empty? # tag consists only of non-printable chars
        # some unicode chars get detected as non-printable and they wont get 
        # displayed unless we replace them with their correspondending html entity
        tag = tag.dump.gsub("\\u{85}", "&hellip;") 

        flags |= TAG_FLAGS[:inspected] if tag.scan(/[[:print:]]/).empty?
      end
    end
      
    flags
  end
  
  def tag_printable
    if tag_is?(:notag)
      self.clan_id.to_s
    elsif tag_is?(:inspected)
      self['tag'].inspect
    else 
      self['tag']
    end
  end
  
  def tag_with_flags
    {:tag => self.tag_printable, :flags => self.tag_flags}
  end
  
  def profile_url
    "#{self.world.url}internal/fight.php?action=watchclan&act_clan_id=#{self.clan_id}"
  end
  
  def changes
    {
      :adds     => self.adds.includes(:user),
      :coleader => self.coleader_changes.includes(:coleader_old, :coleader_new),
      :leader   => self.leader_changes.includes(:leader_old, :leader_new),
      :name     => self.name_changes,
      :tag      => self.tag_changes,
      :outs     => self.outs.includes(:user)
    }
  end
  
  def self.last_update
    self.first.updated_at
  end
end
