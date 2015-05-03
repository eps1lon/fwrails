class SlmaniaController < ApplicationController
  def index
    @slmania_users = Member.slmania_public
  end
  
  def list_actions
    @slmania_user = slmania_user
    
    @alphanumerics = Npc.letters
    
    @entities = Npc.slmania_list
  end
  
  def evaluate_npc
    @slmania_user = slmania_user
    
    @slmania = Slmania.new
    @slmania.user = @slmania_user
    
    @evaluations = @slmania.evaluate(Npc::Conditions.npc(params[:name]))
  end
  
  def evaluate_item
    
  end
  
  def soul_capsule
    
  end
  
  protected
  
  def slmania_user
    # own profile
    if member_signed_in? && current_member.try(:id).eql?(params[:id])
      slmania_user = current_member
    else # somebody else
      slmania_user = Member.slmania_public.where(id: params[:id]).take
    end
    
    # no valid user, redirect to index
    if slmania_user.nil?
      redirect_to slmania_index_url
    end
    
    slmania_user
  end
end
