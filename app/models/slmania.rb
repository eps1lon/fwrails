class Slmania 
  def initialize
    
  end
  
  def blacklist
    @blacklist
  end
  
  def blacklist=(blacklist)
    @blacklist = blacklist
  end
  
  def droplist
    @droplist
  end
  
  def droplist=(droplist)
    @droplist = droplist
  end
  
  def user
    @user
  end
  
  def user=(user)
    @user = user
  end
  
  def evaluate(conditions)
    evaluation = {}
    conditions.each do |key, condition|
      evaluation[key] = {
        # get primaries
        npcs: {
          ids: Npc.where.not(@blacklist || false).where(condition).collect(&:id),
          counts: {}
        },
        drops: ItemsNpc.where(member: @user).includes(:item)
      }
      
      # total action count
      actions = ItemsNpc::defined_enums['action'].keys
      sums = NpcsMember.where(member: @user,
                              npc_id: evaluation[key][:npcs][:ids]).
                        select(actions.map { |action| "SUM(#{action}count) as #{action}count"}).
                        take
      actions.each do |action|
        evaluation[key][:npcs][:counts][action] = sums["#{action}count"]
      end                

      #  drops
      evaluation[key][:drops] = evaluation[key][:drops].where(npc_id: evaluation[key][:npcs][:ids])
      evaluation[key][:drops] = evaluation[key][:drops].where(item_id: @droplist) if @droplist
    end
    
    evaluation
  end
end
