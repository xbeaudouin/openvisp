class Server < ActiveRecord::Migration
  def self.up
    change_table :servers do |t|
      t.column :name,           :string,    :null => false
      t.column :public_name,    :string,    :null => false
      t.column :private_name,   :string
      t.column :description,    :text
      t.column :active,         :integer,   :limit => 1, :null => false, :default => '1'
      t.index  :name, :name => "idx_name"
      t.index  :public_name, :name => "idx_public_name", :unique => true
    end
  end

  def self.down
    remove_column :servers, :name
    remove_column :servers, :public_name
    remove_column :servers, :private_name
    remove_column :servers, :description
    remove_column :servers, :action
  end
end
