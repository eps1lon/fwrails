require 'test_helper'

class Admin::AchievementsControllerTest < ActionController::TestCase
  setup do
    @admin_achievement = admin_achievements(:one)
  end

  test "should get index" do
    get :index
    assert_response :success
    assert_not_nil assigns(:admin_achievements)
  end

  test "should get new" do
    get :new
    assert_response :success
  end

  test "should create admin_achievement" do
    assert_difference('Admin::Achievement.count') do
      post :create, admin_achievement: @admin_achievement.attributes
    end

    assert_redirected_to admin_achievement_path(assigns(:admin_achievement))
  end

  test "should show admin_achievement" do
    get :show, id: @admin_achievement.to_param
    assert_response :success
  end

  test "should get edit" do
    get :edit, id: @admin_achievement.to_param
    assert_response :success
  end

  test "should update admin_achievement" do
    put :update, id: @admin_achievement.to_param, admin_achievement: @admin_achievement.attributes
    assert_redirected_to admin_achievement_path(assigns(:admin_achievement))
  end

  test "should destroy admin_achievement" do
    assert_difference('Admin::Achievement.count', -1) do
      delete :destroy, id: @admin_achievement.to_param
    end

    assert_redirected_to admin_achievements_path
  end
end
