class Domain < ActiveRecord::Migration
  def self.up
    change_table :domains do |t|
      t.column :domain_name,    :string,    :null => false
      t.column :description,    :string,    :null => false, :default => ''
      t.column :aliases,        :integer,   :limit => 11, :null => false, :default => '0'
      t.column :mailboxes,      :integer,   :limit => 11, :null => false, :default => '0'
      t.column :ftp_account,    :integer,   :limit => 11, :null => false, :default => '0'
      t.column :db_count,       :integer,   :limit => 11, :null => false, :default => '0'
      t.column :db_users,       :integer,   :limit => 11, :null => false, :default => '0'
      t.column :db_quota,       :integer,   :limit => 11, :null => false, :default => '0'
      t.column :maxquota,       :integer,   :limit => 11, :null => false, :default => '0'
      t.column :transport,      :string,    :null => true, :default => ''
      t.column :backupmx,       :integer,   :limit => 1, :null => false, :default => '0'
      t.column :antivirus,      :integer,   :limit => 1, :null => false, :default => '0'
      t.column :vrfysender,     :integer,   :limit => 1, :null => false, :default => '0'
      t.column :vrfydomain,     :integer,   :limit => 1, :null => false, :default => '0'
      t.column :greylist,       :integer,   :limit => 1, :null => false, :default => '0'
      t.column :spf,            :integer,   :limit => 1, :null => false, :default => '0'
      t.column :allowchangefwd, :integer,   :limit => 1, :null => false, :default => '0'
      t.column :allowchangepwd, :integer,   :limit => 1, :null => false, :default => '0'
      t.column :active,         :integer,   :limit => 1, :null => false, :default => '1'
      t.column :pdf_pop,        :string,    :null => true, :default => ''
      t.column :pdf_imap,       :string,    :null => true, :default => ''
      t.column :pdf_smtp,       :string,    :null => true, :default => ''
      t.column :pdf_webmail,    :string,    :null => true, :default => ''
      t.column :pdf_custadd,    :string,    :null => true, :default => ''
      t.column :paid,           :integer,   :limit => 1, :null => false, :default => '1'
      t.column :pop3_enabled,   :integer,   :limit => 1, :null => false, :default => '0'
      t.column :imap_enabled,   :integer,   :limit => 1, :null => false, :default => '0'
      t.column :smtp_enabled,   :integer,   :limit => 1, :null => false, :default => '0'
      t.index  :domain_name
    end
  end

  def self.down
    remove_column :domains, :domain_name
    remove_column :domains, :description
    remove_column :domains, :aliases
    remove_column :domains, :mailboxes
    remove_column :domains, :ftp_account
    remove_column :domains, :db_count
    remove_column :domains, :db_users
    remove_column :domains, :db_quota
    remove_column :domains, :maxquota
    remove_column :domains, :transport
    remove_column :domains, :backupmx
    remove_column :domains, :antivirus
    remove_column :domains, :vrfysender
    remove_column :domains, :vrfydomain
    remove_column :domains, :greylist
    remove_column :domains, :spf
    remove_column :domains, :allowchangefwd
    remove_column :domains, :allowchangepwd
    remove_column :domains, :active
    remove_column :domains, :pdf_pop
    remove_column :domains, :pdf_imap
    remove_column :domains, :pdf_smtp
    remove_column :domains, :pdf_webmail
    remove_column :domains, :pdf_custadd
    remove_column :domains, :paid
    remove_column :domains, :pop3_enabled
    remove_column :domains, :imap_enabled
    remove_column :domains, :smtp_enabled
  end
end
