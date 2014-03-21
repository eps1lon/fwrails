class Member < ActiveRecord::Base
  ROLES = %w{developer content_admin}.map &:to_sym

  def is?(role)
    roles.include?(role)
  end

  def roles
    ROLES.reject do |r|
      ((self['roles'].to_i || 0) & 2**ROLES.index(r)).zero?
    end
  end
  
  def roles=(roles)
    self['roles'] = (roles & ROLES).map { |r| 2**ROLES.index(r) }.inject(0, :+)
  end

  def password=(password)
    self['password'] = Digest::MD5.hexdigest(password)
  end
  
  def self.auth?(name, password, role) 
    md5_of_password = Digest::MD5.hexdigest(password)
    member = Member.where(:name => name, :password => md5_of_password).first
    !member.nil? && member.is?(role)
  end
end
