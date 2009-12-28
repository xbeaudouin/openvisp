class HomeController < ApplicationController
  def index
    if defined? session[:username].length
      redirect_to :action => "controlboard"
    end
    # .empty?        #=> true
  end

  def validate
    @accounts = Account.find(:all, :conditions => { :username => params[:account][:username], :password => params[:account][:password], :enabled => "1"})
    if @accounts.count == 1
      session[:username] = @accounts[0].username
      flash[:notice] = "Successfully logged in"  
      redirect_to :action => "index"
    else
      flash[:notice] = "Sorry you have enter invalid credential or your account was disabled"
      redirect_to :action => "index"
    end
  end
  
  def controlboard
    
  end
end
