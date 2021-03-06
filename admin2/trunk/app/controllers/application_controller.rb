# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  helper :all # include all helpers, all the time
  protect_from_forgery # See ActionController::RequestForgeryProtection for details

  # Scrub sensitive parameters from your log
  # filter_parameter_logging :password
  
  def fetch_account_info
    info = Account.find(:first, :conditions => { :username => session[:username], :enabled => "1"})
    return info
  end
  
  def control_admin_privileges
    @account_info = fetch_account_info
    @right = @account_info.right
    if @right.manage != 1
      reset_session
      redirect_to :controller => "home", :action => "index"
    end
  end

end
