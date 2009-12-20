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

ActiveRecord::Schema.define(:version => 20091220180336) do

  create_table "accounts", :force => true do |t|
    t.string   "username"
    t.string   "password"
    t.string   "datetime"
    t.datetime "modified"
    t.integer  "disabled",     :limit => 1
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
  end

end
