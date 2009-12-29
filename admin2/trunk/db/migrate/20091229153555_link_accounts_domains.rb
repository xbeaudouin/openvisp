class LinkAccountsDomains < ActiveRecord::Migration
  def self.up
    create_table :accounts_domains, :id => false, :force => true do |t|
      t.column :account_id,  :integer
      t.column :domain_id,   :integer
      t.column :created_at,  :datetime
      t.column :updated_at,  :datetime
    end
  end

  def self.down
    drop_table :accounts_domains
  end
end
