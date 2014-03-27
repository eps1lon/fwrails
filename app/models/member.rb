class Member < ActiveRecord::Base
  # Include default devise modules. Others available are:
  # :lockable, :timeoutable and :omniauthable, :registerable, :recoverable, :confirmable
  devise :database_authenticatable, :rememberable, :trackable, :validatable  
  
  ROLES = %w{developer content_admin}.map &:to_sym

  def roles
    ROLES.reject do |r|
      ((self['roles'].to_i || 0) & 2**ROLES.index(r)).zero?
    end
  end
  
  def roles=(roles)
    logger.debug roles
    self['roles'] = (roles & ROLES).map { |r| 2**ROLES.index(r) }.inject(0, :+)
  end
  
  # role auth
  def is?(role)
    roles.include?(role)
  end
  
  # role helper
  def developer?
    is?(:developer)
  end
  
  def content_admin?
    is?(:content_admin)
  end
  
  # deprecated
  def self.auth?(role)
    ActiveSupport::Deprecation.warn("Member.auth? is deprecated")
    current_member && current_member.is?(role)
  end
end
