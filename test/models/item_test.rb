require 'test_helper'

class ItemTest < ActiveSupport::TestCase
  test "places" do
    Item.find(1) do |item|
      puts item.positions.to_sql
      assert item.positions
    end
  end
end
