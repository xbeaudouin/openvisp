class CreateServerJobModels < ActiveRecord::Migration
  def self.up
    create_table :server_job_models do |t|

      t.timestamps
    end
  end

  def self.down
    drop_table :server_job_models
  end
end
