require '../test_helper'
require '../../lib/nav'

class NavTest < ActiveSupport::TestCase
  test "vars" do
    nav = Nav.new(1, World.first, {}, 20)
    assert true
  end
end
