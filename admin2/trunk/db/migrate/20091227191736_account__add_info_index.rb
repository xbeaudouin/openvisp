class Account_addInfoIndex < ActiveRecord::Migration
  def self.up
    change_table :accounts do |t|
      t.column :firstname,     :string
      t.column :lastname,     :string
      t.index :username, :name => "idx_username", :unique => true  
      t.index :company, :name => "idx_company"
      t.index :paid, :name => "idx_paid"
      t.index :enabled, :name => "idx_enabled"
    end
  end


  def self.down
    remove_column :firstname, :lastname
    remove_index :idx_username, :idx_company, :idx_paid, :idx_enabled
  end
end


