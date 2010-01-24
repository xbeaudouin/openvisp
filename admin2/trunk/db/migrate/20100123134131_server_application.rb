require 'db/migration_helpers'

class ServerApplication < ActiveRecord::Migration
  extend MigrationHelpers
  def self.up
    change_table :server_applications do |t|
      t.column :name,           :string,    :null => false
      t.column :version,        :string,    :null => false
      t.column :server_job_model_id,   :integer,   :null => false
      t.column :description,    :text
      t.column :active,         :integer,   :limit => 1, :null => false, :default => '1'
    end
    foreign_key :server_applications, :server_job_model_id, :server_job_models, :id, nil, 'CASCADE'
  end

  def self.down
    remove_column :server_applications, :name
    remove_column :server_applications, :version
    remove_column :server_applications, :server_job_model_id
    remove_column :server_applications, :description
    remove_column :server_applications, :active
    drop_foreign_key :server_applications, :job_models
  end
end
