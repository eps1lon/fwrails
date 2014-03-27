class ExtensionsController < ApplicationController
  before_filter do 
    render 'layouts/announced'
  end
  
  def index
    @extensions = []
    #@extensions = Extension.order("name asc")
  end

  def howto
  
  end

  def about
  
  end
  
  def show
  end
end
