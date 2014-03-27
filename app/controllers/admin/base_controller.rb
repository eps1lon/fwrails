class Admin::BaseController < ApplicationController
  protect_from_forgery
  layout "admin"
  
  before_filter do 
    flash[:notice] = "Admin Only Section!!!"
    authenticate_developer!
  end
  
  before_filter do 
    @title = "Adminpanel #{controller_name}##{action_name}"
  end
  
  def generate_dump(model, options = {})
    filename = Rails.root.join("public/dumps/#{ActiveSupport::Inflector.pluralize(model.name.downcase)}.csv")
    File.open(filename, "w", 0664) do |f|
      rows = model.select(options[:only] || "*")
      f.write(rows.to_csv)
    end
  end
end
