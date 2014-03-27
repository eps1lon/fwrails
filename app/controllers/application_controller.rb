class ApplicationController < ActionController::Base  
  protect_from_forgery

  before_filter :auth, :set_locale, :set_nav_controllers
  before_filter do # maintenance
    logger.info @request.env['HTTP_AUTHORIZATION']
    respond_to do |format|
      format.all { render template: "layouts/maintenance", layout: "application" }
    end
  end if ENV['RAILS_ENV'] == 'staging'
  
  # error handler
  unless Rails.application.config.consider_all_requests_local
    rescue_from StandardError, with: :render_500
    rescue_from ActionController::RoutingError, with: :render_404
  end
  
  rescue_from ActiveRecord::RecordNotFound, with: :render_record_not_found
  
  def auth
    std_auth_role = case ENV['RAILS_ENV']
      when 'production' then :public
      when 'staging' then :public 
      else :developer
    end
    @auth_role ||= std_auth_role 
    
    authenticate_or_request_with_http_basic("FWRails needs #{@auth_role.to_s} auth") do |user_name, password|
      Member.auth?(user_name, password, @auth_role)
    end unless @auth_role.eql?(:public)
  end
  
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
      format.all { render template: "layouts/error", layout: "application", status: status }
    end
  end
  
  protected
  # attr order helper
  def order_from_attributes(attributes, param, default = 0)
    order = attributes.select { |attr| attr[:human] == param.to_s.downcase && !attr[:db].nil? }.first
    order ||= attributes[default]
  end 
  
  private
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
end
