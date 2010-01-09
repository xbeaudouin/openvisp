class DomainController < ApplicationController
  def available_locales; AVAILABLE_LOCALES; end

  before_filter :set_locale
  def set_locale
    # if params[:locale] is nil then I18n.default_locale will be used
    I18n.locale = params[:locale]
  end
  
  def index
    control_domain_privileges
    if defined? session[:username].length
      redirect_to :action => "list"
    end
    # .empty?        #=> true
  end

  def translate
    @translation_list_domain = t(:domain_manager_list)
    @translation_add_domain  = t(:domain_manager_add_domain)
    @translation_add_domain_alias = t(:domain_manager_add_alias)
    @translation_view_logs = t(:global_manager_viewlog)
    @translation_back_menu = t(:global_back_home_menu)
  end
  
  def list
    control_domain_privileges
    translate

    @account_info = fetch_account_info
    @welcome_info = "(#{session[:firstname]} #{session[:lastname]})"
    @logout = t(:global_logout)
  end

  def control_domain_privileges
    @account_info = fetch_account_info
    @right = @account_info.right
    if @right.domain != 1
      reset_session
      redirect_to :controller => "home", :action => "index"
    end
  end
  
  def add
    control_domain_privileges
    # Call function with all the translation definition
    translate
    
    if params.has_key?( :account )
      @account = Account.new(params[:account])
        if @account.save
          @quota = Quota.new(params[:quota])
          @quota.account_id = @account.id

          if @quota.save
            @right = Right.new(params[:right])
            @right.account_id = @account.id
            if @right.save
              redirect_to :action => 'list'
            else
              raise ActiveRecord::Rollback
            end

          else
            raise ActiveRecord::Rollback
          end

        else
          render :action => 'add'
        end
    end
    
  end
  
end
