class Domain < ActiveRecord::Base
  has_and_belongs_to_many :accounts
end
