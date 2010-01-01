class Quota < ActiveRecord::Migration
  def self.up
    create_table :quotas, :id => false, :force => true do |t|
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
      t.column :created_at,       :datetime
      t.column :updated_at,       :datetime      
    end

  end

  def self.down
    drop_table :quotas
  end
end
