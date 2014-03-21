class UkimgsController < ApplicationController
  def index
    @areas = Category.areas
    @sets  = Category.sets
    
    @images = []
    
    params[:sets] = (params[:sets] || "").split(",")
    params[:tags] = (params[:tags] || "").split(",")
    
    if !!params[:area_id]
      params[:sets] += [params[:area_id]]
    end
    
    if !!params[:set_id]
      params[:sets] += [params[:set_id]]
    end
    
    params[:sets].uniq!
    
    if params[:sets].length > 0 or params[:tags].length > 0
      @images = Image.includes(:category, :tags)
      
      category_ids = []
      tag_ids = []
      
      if params[:sets].length > 0
        category_ids = Category.where(:name => params[:sets].compact).collect(&:id)
      end
      
      if params[:tags].length > 0
        tag_ids = Tags.where(:name => params[:tags].split(",")).collect(&:id)
      end
      
      # build query
      where = []
      if category_ids.length > 0
        where += [category_ids.map { |c| "category_id = '#{c}'" }.join(" OR ")]
      end
      
      if tag_ids.length > 0
        where += [Images.where(:tags => tag_ids)]
      end
      
      @images = @images.where(where.join(" OR "))
    end
    
    params[:sets] = params[:sets].join(",") || nil
    params[:tags] = params[:tags].join(",") || nil
  end
  
  def tag
    
  end
end
