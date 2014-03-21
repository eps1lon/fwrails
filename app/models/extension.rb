class Extension < ActiveRecord::Base
  STATUS = {
    anounced: 0,
    requested: 1,
    wip: 2,
    permission_pending: 3,
    beta: 4,
    available: 5
  }
  
  def status
    STATUS.key(read_attribute(:status))
  end
 
  def status=(s)
    write_attribute(:status, STATUS[s])
  end

  def downloads
    '?'
  end
end
