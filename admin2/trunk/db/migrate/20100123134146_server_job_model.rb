class ServerJobModel < ActiveRecord::Migration
  def self.up
    change_table :server_job_models do |t|
      t.column :model,       :text,    :null => false
      t.column :description,    :text
      t.column :active,         :integer,   :limit => 1, :null => false, :default => '1'
    end
  end

  def self.down
    remove_column :server_job_models, :model
    remove_column :server_job_models, :description
    remove_column :server_job_models, :active
  end
end
