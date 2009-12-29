class Account < ActiveRecord::Migration
  def self.up
	  change_table :accounts do |t|
      t.column :username,     :string
      t.column :password,     :string
      t.column :enabled,     :integer, :limit => 1
      t.column :tech,         :integer, :limit => 1
      t.column :company,      :string
      t.column :address,      :string
      t.column :city,         :string
      t.column :postal_code,  :string, :limit => 40
      t.column :weburl,       :string
      t.column :email,        :string
      t.column :phone,        :string, :limit => 50
      t.column :fax,          :string, :limit => 50
      t.column :logo,         :string
      t.column :emailsupport, :string, :limit => 150
      t.column :phonesupport, :string, :linit => 50
      t.column :websupport,   :string
      t.column :webfaq,       :string
      t.column :paid,         :integer, :limit => 1
    end  
  end


  def self.down
    remove_column :accounts, :username
    remove_column :accounts, :password
    remove_column :accounts, :enabled
    remove_column :accounts, :tech
    remove_column :accounts, :company
    remove_column :accounts, :address
    remove_column :accounts, :city
    remove_column :accounts, :postal_code
    remove_column :accounts, :weburl
    remove_column :accounts, :email
    remove_column :accounts, :phone
    remove_column :accounts, :fax
    remove_column :accounts, :logo
    remove_column :accounts, :emailsupport
    remove_column :accounts, :phonesupport
    remove_column :accounts, :websupport
    remove_column :accounts, :webfaq
    remove_column :accounts, :paid
  end
end


