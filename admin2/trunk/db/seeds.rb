# This file should contain all the record creation needed to seed the database with its default values.
# The data can then be loaded with the rake db:seed (or created alongside the db with db:setup).
#
# Examples:
#   
#   cities = City.create([{ :name => 'Chicago' }, { :name => 'Copenhagen' }])
#   Major.create(:name => 'Daley', :city => cities.first)

Account.create(:username => 'admin@ova.local', :password => 'admin', :tech => '1',
               :enabled => '1', :paid => "1", :firstname => "Admin", :lastname => "OVA"
               )
               
Domain.create(:domain_name => 'ova.local', :description => 'Domain for managing OVA' )

@accounts = Account.find(:all, :conditions => { :username => 'admin@ova.local' })
@domains = Domain.find(:all, :conditions => { :domain_name => 'ova.local' })

Accounts_domain.create(:account_id => @accounts[0].id, :domain_id => @domains[0].id)

Right.create(:account_id => @accounts[0].id, :manage => '1')
Quota.create(:account_id => @accounts[0].id)

require 'active_record/fixtures'
Fixtures.create_fixtures("#{Rails.root}/test/fixtures", "ovaconfigs")  