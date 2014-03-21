class Admin::BaseController < ActionController::Base
  protect_from_forgery
  layout "admin"
  
  before_filter do 
    authenticate_or_request_with_http_basic('admin') do |user_name, password|
      Member.auth?(user_name, password, :content_admin)
    end
  end
  
  def generate_dump(model, options = {})
    filename = Rails.root.join("public/dumps/#{ActiveSupport::Inflector.pluralize(model.name.downcase)}.csv")
    File.open(filename, "w", 0664) do |f|
      rows = model.select(options[:only] || "*")
      f.write(rows.to_csv)
    end
  end
end
