class SessionsController < Devise::SessionsController
  skip_before_filter :staging, :only => [:new]
end