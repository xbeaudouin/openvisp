class CreateServerIpAddresses < ActiveRecord::Migration
  def self.up
    create_table :server_ip_addresses do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :server_ip_addresses
  end
end
