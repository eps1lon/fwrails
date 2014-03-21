class Race < ActiveRecord::Base
  has_many :users
  has_many :outs, :class_name => 'UsersRaceChange'
  has_many :adds, :class_name => 'UsersRaceChange'
  
  has_one :place
  
  def short
    return self.name if self['short'].blank?
    self['short']
  end
end
