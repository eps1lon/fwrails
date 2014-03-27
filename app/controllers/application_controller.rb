class ApplicationController < ActionController::Base  
  protect_from_forgery

  before_filter :auth, :set_locale, :set_nav_controllers
  before_filter do # maintenance
    respond_to do |format|
      format.all { render template: "layouts/maintenance", layout: "application" }
    end
  end if ENV['RAILS_ENV'] == 'staging'
  
  # error handler
  rescue_from ActiveRecord::RecordNotFound,   with: :render_record_not_found
  
  unless Rails.application.config.consider_all_requests_local
    rescue_from ActionController::RoutingError, with: :render_404
    rescue_from StandardError, with: :render_500
  end
  
  def auth
    std_auth_role = case ENV['RAILS_ENV']
      when 'production' then :public
      else :developer
    end
    @auth_role ||= std_auth_role 
    
    authenticate_or_request_with_http_basic("FWRails needs #{@auth_role.to_s} auth") do |user_name, password|
      Member.auth?(user_name, password, @auth_role)
    end unless @auth_role.eql?(:public)
  end
  
  def set_nav_controllers
    @controllers = %w{users clans graphs achievements}
  end
  
  def set_locale
    @locales = %w{de}
    @locale = request.subdomains.first
    
    unless @locales.include?(@locale)
      @locale = I18n.default_locale
    end
    
    I18n.locale = @locale
  end
  
  #def default_url_options(options={})
  #  logger.debug "default_url_options is passed options: #{options.inspect}\n"
  #  { :locale => I18n.locale }
  #end
  
  protected
  
  # attr order helper
  def order_from_attributes(attributes, param, default = 0)
    order = attributes.select { |attr| attr[:human] == param.to_s.downcase && !attr[:db].nil? }.first
    order ||= attributes[default]
  end
  
  # error pages
  
  def render_error_page(status) 
    respond_to do |format|
      format.all { render template: "layouts/error", layout: "application", status: status }
    end
  end
  
  def render_record_not_found
    @partial = "record_not_found"
    render_error_page 404
  end
  
  def render_404(exception)
    @not_found_path = exception.message
    render_error_page 404
  end
  
  def render_500(exception)
    @error = exception
    render_error_page 500
  end
end
