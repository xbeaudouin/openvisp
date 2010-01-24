class CreateServerApplications < ActiveRecord::Migration
  def self.up
    create_table :server_applications do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :server_applications
  end
end
