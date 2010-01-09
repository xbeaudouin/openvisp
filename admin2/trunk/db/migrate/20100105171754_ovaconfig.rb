class Ovaconfig < ActiveRecord::Migration
  def self.up
    change_table :ovaconfigs do |t|
      t.column :item,       :string,    :null => false
      t.column :value,      :string,    :null => false
      t.column :values,     :string
      t.column :comment,    :text
    end
  end

  def self.down
    remove_column :ovaconfigs, :item
    remove_column :ovaconfigs, :value
    remove_column :ovaconfigs, :values
    remove_column :ovaconfigs, :comment
  end
end
