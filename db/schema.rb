# encoding: UTF-8
# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your
# database schema. If you need to create the application database on another
# system, you should be using db:schema:load, not running all the migrations
# from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended that you check this file into your version control system.

ActiveRecord::Schema.define(version: 20140324183515) do

  create_table "achievements", id: false, force: true do |t|
    t.string   "name"
    t.string   "gfx",            default: "", null: false
    t.text     "description"
    t.integer  "stage",          default: 1,  null: false
    t.integer  "max_stage",      default: 1
    t.integer  "reward"
    t.integer  "needed"
    t.integer  "achievement_id", default: 0,  null: false
    t.datetime "created_at"
  end

  create_table "categories", force: true do |t|
    t.string   "name"
    t.integer  "category_type"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "clans", id: false, force: true do |t|
    t.integer  "clan_id",        default: 0, null: false
    t.integer  "world_id",       default: 0, null: false
    t.string   "name",                       null: false
    t.string   "tag",                        null: false
    t.integer  "leader_id",                  null: false
    t.integer  "coleader_id",                null: false
    t.integer  "sum_experience"
    t.integer  "member_count"
    t.datetime "created_at"
  end

  create_table "clans_coleader_changes", id: false, force: true do |t|
    t.integer  "clan_id",         default: 0,     null: false
    t.integer  "world_id",        default: 0,     null: false
    t.integer  "coleader_id_old"
    t.integer  "coleader_id_new"
    t.datetime "created_at",                      null: false
    t.boolean  "deleted",         default: false, null: false
  end

  create_table "clans_deletes", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.string   "tag"
    t.datetime "created_at",             null: false
  end

  create_table "clans_leader_changes", id: false, force: true do |t|
    t.integer  "clan_id",       default: 0,     null: false
    t.integer  "world_id",      default: 0,     null: false
    t.integer  "leader_id_old"
    t.integer  "leader_id_new"
    t.datetime "created_at",                    null: false
    t.boolean  "deleted",       default: false, null: false
  end

  create_table "clans_name_changes", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.string   "name_old"
    t.string   "name_new"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  create_table "clans_news", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.string   "tag"
    t.datetime "created_at",             null: false
  end

  create_table "clans_old", id: false, force: true do |t|
    t.integer  "clan_id",        default: 0, null: false
    t.integer  "world_id",       default: 0, null: false
    t.string   "name",                       null: false
    t.string   "tag",                        null: false
    t.integer  "leader_id",                  null: false
    t.integer  "coleader_id",                null: false
    t.integer  "sum_experience"
    t.integer  "member_count"
    t.datetime "created_at"
  end

  create_table "clans_tag_changes", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.string   "tag_old"
    t.string   "tag_new"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  create_table "experience_changes", id: false, force: true do |t|
    t.integer  "user_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.integer  "experience", default: 0, null: false
    t.datetime "created_at",             null: false
  end

  create_table "extensions", force: true do |t|
    t.string   "name"
    t.text     "desc"
    t.string   "filename"
    t.integer  "rating"
    t.integer  "ratings",    default: 0, null: false
    t.integer  "downloads",  default: 0, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
    t.integer  "status",     default: 0, null: false
  end

  create_table "images", force: true do |t|
    t.integer  "category_id"
    t.string   "filename"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "images_tags", id: false, force: true do |t|
    t.integer  "image_id",   default: 0, null: false
    t.integer  "tag_id",     default: 0, null: false
    t.integer  "votes_down"
    t.integer  "votes_up"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "languages", force: true do |t|
    t.string   "country_code"
    t.string   "language_code"
    t.string   "tld"
    t.datetime "created_at"
  end

  create_table "members", force: true do |t|
    t.string   "mail",                   null: false
    t.string   "name",                   null: false
    t.string   "password",               null: false
    t.integer  "roles",      default: 0, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "npcs", force: true do |t|
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "name"
    t.string   "desc"
    t.string   "gfx"
    t.integer  "pos_x"
    t.integer  "pos_y"
    t.integer  "unique_npc"
    t.integer  "live"
    t.integer  "strength"
    t.integer  "maxdmg"
    t.integer  "flags"
    t.integer  "killcount"
  end

  create_table "other_changes", id: false, force: true do |t|
    t.integer  "user_id",    null: false
    t.integer  "world_id",   null: false
    t.string   "old"
    t.string   "new"
    t.integer  "type",       null: false
    t.datetime "created_at", null: false
  end

  create_table "places", force: true do |t|
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "name"
    t.string   "desc"
    t.string   "gfx"
    t.integer  "pos_x"
    t.integer  "pos_y"
    t.integer  "flags"
    t.integer  "area_id"
  end

  add_index "places", ["area_id"], name: "index_places_on_area_id", using: :btree

  create_table "races", force: true do |t|
    t.string   "name"
    t.integer  "base_live"
    t.integer  "base_strength"
    t.integer  "base_intelligence"
    t.integer  "place_id"
    t.integer  "flags"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "statistic_changes", id: false, force: true do |t|
    t.integer  "statistic_id",           default: 0, null: false
    t.integer  "world_id",               default: 0, null: false
    t.integer  "value",        limit: 8, default: 0
    t.datetime "created_at",                         null: false
  end

  create_table "statistics", force: true do |t|
    t.string   "name"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "stock_changes", id: false, force: true do |t|
    t.integer  "stock_id",   default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.integer  "value"
    t.integer  "not_null"
    t.datetime "created_at",             null: false
  end

  create_table "stocks", force: true do |t|
    t.string   "name"
    t.datetime "created_at"
  end

  create_table "tags", force: true do |t|
    t.string   "name"
    t.boolean  "reported"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "users", id: false, force: true do |t|
    t.integer  "user_id",    default: 0,  null: false
    t.integer  "world_id",   default: 0,  null: false
    t.integer  "clan_id"
    t.integer  "race_id"
    t.string   "name",       default: ""
    t.integer  "experience", default: 0,  null: false
    t.datetime "created_at"
  end

  create_table "users_achievements", id: false, force: true do |t|
    t.integer  "user_id",        default: 0,     null: false
    t.integer  "world_id",       default: 0,     null: false
    t.integer  "achievement_id", default: 0,     null: false
    t.integer  "progress"
    t.datetime "created_at"
    t.boolean  "deleted",        default: false, null: false
    t.integer  "stage",          default: 0
  end

  create_table "users_achievements_caches", id: false, force: true do |t|
    t.integer  "user_id",          default: 0,     null: false
    t.integer  "world_id",         default: 0,     null: false
    t.integer  "count"
    t.integer  "reward_collected"
    t.datetime "created_at"
    t.boolean  "deleted",          default: false, null: false
  end

  create_table "users_achievements_changes", id: false, force: true do |t|
    t.integer  "user_id",        default: 0,     null: false
    t.integer  "world_id",       default: 0,     null: false
    t.integer  "achievement_id", default: 0,     null: false
    t.integer  "progress"
    t.datetime "created_at",                     null: false
    t.boolean  "deleted",        default: false, null: false
  end

  create_table "users_achievements_old", id: false, force: true do |t|
    t.integer  "user_id",        default: 0,     null: false
    t.integer  "world_id",       default: 0,     null: false
    t.integer  "achievement_id", default: 0,     null: false
    t.integer  "progress"
    t.datetime "created_at"
    t.boolean  "deleted",        default: false, null: false
    t.integer  "stage",          default: 0
  end

  create_table "users_clan_changes", id: false, force: true do |t|
    t.integer  "user_id",     default: 0,     null: false
    t.integer  "world_id",    default: 0,     null: false
    t.integer  "clan_id_old"
    t.integer  "clan_id_new"
    t.datetime "created_at",                  null: false
    t.boolean  "deleted",     default: false, null: false
  end

  create_table "users_deletes", id: false, force: true do |t|
    t.integer  "user_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.string   "name"
    t.datetime "created_at",             null: false
  end

  create_table "users_experience_changes", id: false, force: true do |t|
    t.integer  "user_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.integer  "experience"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  create_table "users_name_changes", id: false, force: true do |t|
    t.integer  "user_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.string   "name_old"
    t.string   "name_new"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  create_table "users_news", id: false, force: true do |t|
    t.integer  "user_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.string   "name"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  create_table "users_old", id: false, force: true do |t|
    t.integer  "user_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.integer  "clan_id",                null: false
    t.integer  "race_id",                null: false
    t.string   "name",                   null: false
    t.integer  "experience",             null: false
    t.datetime "created_at"
  end

  create_table "users_race_changes", id: false, force: true do |t|
    t.integer  "user_id",     default: 0,     null: false
    t.integer  "world_id",    default: 0,     null: false
    t.integer  "race_id_old"
    t.integer  "race_id_new"
    t.datetime "created_at",                  null: false
    t.boolean  "deleted",     default: false, null: false
  end

  create_table "worlds", force: true do |t|
    t.string   "name"
    t.string   "subdomain"
    t.string   "short"
    t.integer  "language_id"
    t.datetime "created_at"
  end

  create_table "worlds_achievements_changes", id: false, force: true do |t|
    t.integer  "achievement_id", default: 0, null: false
    t.integer  "world_id",       default: 0, null: false
    t.integer  "progress"
    t.datetime "created_at",                 null: false
  end

end
