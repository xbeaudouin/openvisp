class Account < ActiveRecord::Base
  has_and_belongs_to_many :domains
  has_one :right
  has_one :quota
end
