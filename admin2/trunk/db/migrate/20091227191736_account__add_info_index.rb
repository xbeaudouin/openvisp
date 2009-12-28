class Account_addInfoIndex < ActiveRecord::Migration
  def self.up
    change_table :accounts do |t|
      t.column :firstname,     :string
      t.column :lastname,     :string
      t.index :username
      t.index :company
      t.index :paid
      t.index :enabled
    end
  end


  def self.down
    remove_column :firstname, :lastname
    remove_index :username, :company, :paid, :enabled
  end
end


