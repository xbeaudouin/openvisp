class CreateOvaconfigs < ActiveRecord::Migration
  def self.up
    create_table :ovaconfigs do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :ovaconfigs
  end
end
