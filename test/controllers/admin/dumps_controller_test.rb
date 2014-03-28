require 'test_helper'

class Admin::DumpsControllerTest < ActionController::TestCase
  setup do
    @admin_dump = admin_dumps(:one)
  end

  test "should get index" do
    get :index
    assert_response :success
    assert_not_nil assigns(:admin_dumps)
  end

  test "should get new" do
    get :new
    assert_response :success
  end

  test "should create admin_dump" do
    assert_difference('Admin::Dump.count') do
      post :create, admin_dump: {  }
    end

    assert_redirected_to admin_dump_path(assigns(:admin_dump))
  end

  test "should show admin_dump" do
    get :show, id: @admin_dump
    assert_response :success
  end

  test "should get edit" do
    get :edit, id: @admin_dump
    assert_response :success
  end

  test "should update admin_dump" do
    patch :update, id: @admin_dump, admin_dump: {  }
    assert_redirected_to admin_dump_path(assigns(:admin_dump))
  end

  test "should destroy admin_dump" do
    assert_difference('Admin::Dump.count', -1) do
      delete :destroy, id: @admin_dump
    end

    assert_redirected_to admin_dumps_path
  end
end
