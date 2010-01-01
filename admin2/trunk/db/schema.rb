# This file is auto-generated from the current state of the database. Instead of editing this file, 
# please use the migrations feature of Active Record to incrementally modify your database, and
# then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your database schema. If you need
# to create the application database on another system, you should be using db:schema:load, not running
# all the migrations from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended to check this file into your version control system.

ActiveRecord::Schema.define(:version => 20100101150031) do

  create_table "accounts", :force => true do |t|
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "username"
    t.string   "password"
    t.integer  "enabled",      :limit => 1
    t.integer  "tech",         :limit => 1
    t.string   "company"
    t.string   "address"
    t.string   "city"
    t.string   "postal_code",  :limit => 40
    t.string   "weburl"
    t.string   "email"
    t.string   "phone",        :limit => 50
    t.string   "fax",          :limit => 50
    t.string   "logo"
    t.string   "emailsupport", :limit => 150
    t.string   "phonesupport"
    t.string   "websupport"
    t.string   "webfaq"
    t.integer  "paid",         :limit => 1
    t.string   "firstname"
    t.string   "lastname"
  end

  add_index "accounts", ["company"], :name => "idx_company"
  add_index "accounts", ["enabled"], :name => "idx_enabled"
  add_index "accounts", ["paid"], :name => "idx_paid"
  add_index "accounts", ["username"], :name => "idx_username", :unique => true

  create_table "accounts_domains", :id => false, :force => true do |t|
    t.integer  "account_id"
    t.integer  "domain_id"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "domains", :force => true do |t|
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "domain_name",                                 :null => false
    t.string   "description",                 :default => "", :null => false
    t.integer  "aliases",                     :default => 0,  :null => false
    t.integer  "mailboxes",                   :default => 0,  :null => false
    t.integer  "ftp_account",                 :default => 0,  :null => false
    t.integer  "db_count",                    :default => 0,  :null => false
    t.integer  "db_users",                    :default => 0,  :null => false
    t.integer  "db_quota",                    :default => 0,  :null => false
    t.integer  "maxquota",                    :default => 0,  :null => false
    t.string   "transport",                   :default => ""
    t.integer  "backupmx",       :limit => 1, :default => 0,  :null => false
    t.integer  "antivirus",      :limit => 1, :default => 0,  :null => false
    t.integer  "vrfysender",     :limit => 1, :default => 0,  :null => false
    t.integer  "vrfydomain",     :limit => 1, :default => 0,  :null => false
    t.integer  "greylist",       :limit => 1, :default => 0,  :null => false
    t.integer  "spf",            :limit => 1, :default => 0,  :null => false
    t.integer  "allowchangefwd", :limit => 1, :default => 0,  :null => false
    t.integer  "allowchangepwd", :limit => 1, :default => 0,  :null => false
    t.integer  "active",         :limit => 1, :default => 1,  :null => false
    t.string   "pdf_pop",                     :default => ""
    t.string   "pdf_imap",                    :default => ""
    t.string   "pdf_smtp",                    :default => ""
    t.string   "pdf_webmail",                 :default => ""
    t.string   "pdf_custadd",                 :default => ""
    t.integer  "paid",           :limit => 1, :default => 1,  :null => false
    t.integer  "pop3_enabled",   :limit => 1, :default => 0,  :null => false
    t.integer  "imap_enabled",   :limit => 1, :default => 0,  :null => false
    t.integer  "smtp_enabled",   :limit => 1, :default => 0,  :null => false
  end

  add_index "domains", ["domain_name"], :name => "idx_domain_name", :unique => true
  add_index "domains", ["imap_enabled"], :name => "idx_imap_enabled"
  add_index "domains", ["paid"], :name => "idx_paid"
  add_index "domains", ["pdf_imap"], :name => "idx_pdf_imap"
  add_index "domains", ["pdf_pop"], :name => "idx_pdf_pop"
  add_index "domains", ["pdf_smtp"], :name => "idx_pdf_smtp"
  add_index "domains", ["pdf_webmail"], :name => "idx_webmail"
  add_index "domains", ["pop3_enabled"], :name => "idx_pop3_enabled"
  add_index "domains", ["smtp_enabled"], :name => "idx_smtp_enabled"
  add_index "domains", ["transport"], :name => "idx_transport"

  create_table "quotas", :id => false, :force => true do |t|
    t.integer  "account_id",   :null => false
    t.integer  "diskspace",    :null => false
    t.integer  "ftp",          :null => false
    t.integer  "dbcount",      :null => false
    t.integer  "dbuser",       :null => false
    t.integer  "domains",      :null => false
    t.integer  "emails",       :null => false
    t.integer  "emails_alias", :null => false
    t.integer  "http",         :null => false
    t.integer  "http_alias",   :null => false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "rights", :force => true do |t|
    t.datetime "created_at"
    t.datetime "updated_at"
    t.integer  "account_id",                                    :null => false
    t.integer  "mail",              :limit => 1, :default => 0, :null => false
    t.integer  "datacenter",        :limit => 1, :default => 0, :null => false
    t.integer  "datacenter_manage", :limit => 1, :default => 0, :null => false
    t.integer  "ftp",               :limit => 1, :default => 0, :null => false
    t.integer  "http",              :limit => 1, :default => 0, :null => false
    t.integer  "domain",            :limit => 1, :default => 0, :null => false
    t.integer  "postgresql",        :limit => 1, :default => 0, :null => false
    t.integer  "mysql",             :limit => 1, :default => 0, :null => false
    t.integer  "manage",            :limit => 1, :default => 0, :null => false
  end

end
