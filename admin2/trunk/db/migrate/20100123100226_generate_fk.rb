require 'db/migration_helpers'

class GenerateFk < ActiveRecord::Migration
  extend MigrationHelpers
  
  def self.up
    foreign_key :accounts_domains, :account_id, :accounts, :id, nil, 'CASCADE'
    foreign_key :accounts_domains, :domain_id, :domains, :id, nil, 'CASCADE'
    foreign_key :rights, :account_id, :accounts, :id, nil, 'CASCADE'
    foreign_key :quotas, :account_id, :accounts, :id, nil, 'CASCADE'
  end

  def self.down
    drop_foreign_key :accounts_domains, :domains
    drop_foreign_key :accounts_domains, :accounts
    drop_foreign_key :rights, :accounts
    drop_foreign_key :quotas, :accounts
  end
end
