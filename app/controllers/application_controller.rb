class ApplicationController < ActionController::Base  
  protect_from_forgery
  
  before_filter :set_view_vars, :set_nav_controllers
  before_filter :set_locale
  before_filter :update_cache_location, :staging
  
  # error handler
  unless Rails.application.config.consider_all_requests_local
    rescue_from StandardError, with: :render_500
    rescue_from ActionController::RoutingError, with: :render_404
  end
  
  rescue_from ActiveRecord::RecordNotFound, with: :render_record_not_found
 
  # error pages
  def routing_error
    raise ActionController::RoutingError.new(params[:path])
  end
  
  def render_record_not_found(record)
    @partial = "record_not_found"
    @record = record
    render_error_page 404
  end
  
  def render_404(path)
    @path = path
    render_error_page 404
  end
  
  def render_500(exception)
    @error = exception
    render_error_page 500
  end
  
  def render_error_page(status) 
    respond_to do |format|
      format.all { render template: "layouts/error", formats: "html", layout: "application", status: status }
    end
  end
  
  protected
  def current_subdomain
    request.subdomains.first
  end
  
  # filters sql by in params
  def filter_sql_by(hash, key, std = :desc) 
    hash[key] = std unless %w{asc desc}.include?(hash[key])
    hash
  end
  
  # attr order helper
  def order_from_attributes(attributes, param, default = 0)
    order = attributes.select { |attr| attr[:human] == param.to_s.downcase && !attr[:db].nil? }.first
    order ||= attributes[default]
  end 
  
  # devise helpers
  def authenticate_developer!
    authenticate_member! unless current_member.try(:developer?)
  end
  
  def authenticate_content_admin!
    authenticate_member! unless current_member.try(:content_admin?)
  end
  
  def update_cache_location
    path = [Rails.root, 'tmp', 'cache', current_subdomain]
    Freewar3::Application.config.action_controller.page_cache_directory = File.join(path.compact)
  end
  
  def set_nav_controllers
    @controllers = %w{users clans graphs achievements npcs}
  end
  
  def set_locale
    @locales = Language.all.map(&:language_code)
    @language = Language.find_by_language_code(current_subdomain)
    @locale = @language.language_code
    
    I18n.locale = @locale
  end
  
  def set_view_vars
    @stylesheets = [] << (controller_name if asset_exists?("#{controller_name}.css"))
    @javascripts = [] << (controller_name if asset_exists?("#{controller_name}.js"))
    
    # actions of spotlight controller
    @spotlights = %w{user clan}
  end
  
  # 
  def staging
    respond_to do |format|
      format.all { render template: "layouts/maintenance", layout: "application" }
    end if ENV['RAILS_ENV'] == 'staging' && !current_member.try(:developer?)
  end
  
  # Returns true if an asset exists in the Asset Pipeline, false if not.
  def asset_exists?(path)
    begin
      pathname = Rails.application.assets.resolve(path)
      return !!pathname # double-bang turns String into boolean
    rescue Sprockets::FileNotFound
      return false
    end
  end
end
