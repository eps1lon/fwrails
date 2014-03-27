class HomeController < ApplicationController
  def index
    @news = News.order("created_at DESC")
    
    @news = @news.published unless current_member.try(:developer?)
  end
  
  def show
    @news = News.find(params[:id])
    
    raise ActiveRecord::RecordNotFound unless @news.try(:published?) || current_member.try(:developer?)
  end
  
end
