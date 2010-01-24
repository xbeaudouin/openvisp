class ServerIpAddress < ActiveRecord::Migration
  def self.up
    change_table :server_ip_addresses do |t|
      t.column :public,       :string,    :limit => 15, :null => false
      t.column :private,      :string,    :limit => 15
      t.column :hostname,     :string,    :limit => 15, :null => false
      t.column :active,       :integer,   :limit => 1, :null => false, :default => '1'
      t.column :comment,      :text
    end
  end

  def self.down
    remove_column :server_ip_addresses, :public
    remove_column :server_ip_addresses, :private
    remove_column :server_ip_addresses, :hostname
    remove_column :server_ip_addresses, :active
    remove_column :server_ip_addresses, :comment
  end
end
