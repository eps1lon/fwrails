class ExtensionsController < ApplicationController
  before_filter do 
    flash[:notice] = I18n.t("dev.announced")
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
