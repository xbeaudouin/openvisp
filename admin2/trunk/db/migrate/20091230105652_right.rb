class Right < ActiveRecord::Migration
  def self.up
    change_table :rights do |t|
      t.column :account_id,         :integer,    :null => false
      t.column :mail,               :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :datacenter,         :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :datacenter_manage,  :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :ftp,                :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :http,               :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :domain,             :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :postgresql,         :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :mysql,              :integer,    :limit => 1,  :null => false, :default => '0'
      t.column :manage,             :integer,    :limit => 1,  :null => false, :default => '0'
    end
  end

  def self.down
    remove_column :rights,  :account_id
    remove_column :rights,  :mail
    remove_column :rights,  :datacenter
    remove_column :rights,  :datacenter_manage
    remove_column :rights,  :ftp
    remove_column :rights,  :http
    remove_column :rights,  :domain
    remove_column :rights,  :postgresql
    remove_column :rights,  :mysql
    remove_column :rights,  :manage
  end
end
