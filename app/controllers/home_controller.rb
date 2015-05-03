class HomeController < ApplicationController
  def about   
  end
  
  def contact
    
  end
  
  def dumps
    @dumps = Dump.all
    @dumps = @dumps.shared unless current_member.try(:developer)
    
    if params[:path]
      @dumps = @dumps.where(:path => params[:path]).take
      
      @dumps_mapped = []
      files = @dumps.try(:files)
      
      files.sort.each do |file|
        dump_file = @dumps.dup
        dump_file.path = file
        @dumps_mapped << dump_file
      end if files
      
      @dumps = @dumps_mapped
    end
  end
  
  def index
    @news = News.order("created_at DESC")
    
    @news = @news.published unless current_member.try(:developer?)
  end
  
  def show
    @news = News.find(params[:id])
    
    raise ActiveRecord::RecordNotFound unless @news.try(:published?) || current_member.try(:developer?)
  end
  
end
