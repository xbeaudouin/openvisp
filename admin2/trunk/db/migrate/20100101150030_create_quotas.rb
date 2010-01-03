class CreateQuotas < ActiveRecord::Migration
  def self.up
    create_table :quotas do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :quotas
  end
end