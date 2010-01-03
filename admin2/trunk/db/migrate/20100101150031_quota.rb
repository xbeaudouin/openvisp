class Quota < ActiveRecord::Migration
  def self.up
    change_table :quotas do |t|
      t.column :account_id,       :integer,    :null => false
      t.column :diskspace,        :integer,    :null => false
      t.column :ftp,              :integer,    :null => false
      t.column :dbcount,          :integer,    :null => false
      t.column :dbuser,           :integer,    :null => false
      t.column :domains,          :integer,    :null => false
      t.column :emails,           :integer,    :null => false
      t.column :emails_alias,     :integer,    :null => false
      t.column :http,             :integer,    :null => false
      t.column :http_alias,       :integer,    :null => false 
    end

  end

  def self.down
    remove_column :quotas,  :account_id
    remove_column :quotas,  :diskspace
    remove_column :quotas,  :ftp
    remove_column :quotas,  :dbcount
    remove_column :quotas,  :dbuser
    remove_column :quotas,  :domains
    remove_column :quotas,  :emails
    remove_column :quotas,  :emails_alias
    remove_column :quotas,  :http
    remove_column :quotas,  :http_alias
  end
end
