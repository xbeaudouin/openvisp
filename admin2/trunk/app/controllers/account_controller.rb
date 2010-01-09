class AccountController < ApplicationController
  def available_locales; AVAILABLE_LOCALES; end

  before_filter :set_locale
  def set_locale
    # if params[:locale] is nil then I18n.default_locale will be used
    I18n.locale = params[:locale]
  end

  def translate
    # Add translation definition for items
    @translation_disabled = t(:global_disabled)
    @translation_enabled = t(:global_enabled)
    @translation_disable = t(:global_disable)
    @translation_enable = t(:global_enable)
    @translation_delete = t(:global_delete)
    @translation_modify = t(:global_modify)
    @translation_delete = t(:global_delete)
    @translation_back_menu = t(:global_back_home_menu)
    @translation_list_account = t(:account_manager_list_accounts)
    @translation_add_account = t(:account_manager_add_account)
    @translation_viewlogs = t(:account_manager_viewlog)
    @translation_backup = t(:account_manager_backup)
    @translation_import_accounts = t(:account_manager_import_accounts)
    @translation_add_user = t(:account_manager_add_user)
    @translation_save = t(:global_save)

        
  end
  
  def list
    control_admin_privileges
    # Call function with all the translation definition
    translate

    # Data Object 
    @accounts = Account.find( :all )
  
  end

  def disable
    control_admin_privileges
    @account = Account.find(params[:id])
    @account[:enabled] = 0
    @account.save
    redirect_to :action => "list"
  end

  def enable
    control_admin_privileges
    @account = Account.find(params[:id])
    @account[:enabled] = 1
    @account.save
    redirect_to :action => "list"
  end

	def index
		redirect_to :action => "list"
	end

  def add
    control_admin_privileges
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

  def modify
    control_admin_privileges
    # Call function with all the translation definition
    translate
    @account = Account.find(params[:id])

    @quota_form = Quota.find(:first, :conditions => { :account_id => params[:id] })
    @right_form = Right.find(:first, :conditions => { :account_id => params[:id] })

    
    if params.has_key?( :account ) and @account.update_attributes(params[:account]) and @right_form.update_attributes(params[:right]) and @quota_form.update_attributes(params[:quota])
      redirect_to :action => 'list'
    end
  end

  
  def delete
    control_admin_privileges
  end

end
