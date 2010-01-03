class HomeController < ApplicationController
  def available_locales; AVAILABLE_LOCALES; end

  before_filter :set_locale
  def set_locale
    # if params[:locale] is nil then I18n.default_locale will be used
    I18n.locale = params[:locale]
  end
  
  def index
    if defined? session[:username].length
      redirect_to :action => "controlboard"
    end
    # .empty?        #=> true
  end

  def validate
    @accounts = Account.find(:first, :conditions => { :username => params[:account][:username], :password => params[:account][:password], :enabled => "1"})
    if !@accounts.nil?
#      @account = @accounts[0]
      session[:username] = @accounts.username
      session[:lastname] = @accounts.lastname
      session[:firstname] = @accounts.firstname
      flash[:notice] = 'Successfully logged in'  
      redirect_to :action => "index"
    else
      flash[:notice] = 'Sorry you have enter invalid credential or your account was disabled'
      redirect_to :action => "index"
    end
  end
  
  def controlboard
    
    @account_info = fetch_account_info
    
    @welcome_info = "(#{session[:firstname]} #{session[:lastname]})"
    @translation_change_forward = t(:control_board_mailbox_forward)
    @translation_edit_filter = t(:control_board_mailbox_filter)
    @logout = t(:global_logout)
  end
  
  def logout
    reset_session  
    flash[:notice] = 'Successfully logged out'  
    redirect_to :action => "index"
  end

  
end
