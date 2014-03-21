class ExtensionsController < ApplicationController
  def index
  
  end

  def howto
  
  end

  def about
  
  end

  def list
    @extensions = Extension.order("name asc")
  end
  
  def show
    
  end
end
