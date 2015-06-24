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

ActiveRecord::Schema.define(version: 20150610152738) do

  create_table "abilities", force: true do |t|
    t.string   "name",                   null: false
    t.text     "desc",                   null: false
    t.integer  "basetime",   default: 0, null: false
    t.integer  "max_stage",  default: 1, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

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

  create_table "admin_news", force: true do |t|
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "areas", force: true do |t|
    t.string   "name"
    t.integer  "type",       default: 0, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "areas", ["name"], name: "index_areas_on_name", unique: true, using: :btree

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

  add_index "clans_coleader_changes", ["created_at"], name: "index_clans_coleader_changes_on_created_at", using: :btree

  create_table "clans_deletes", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.string   "tag"
    t.datetime "created_at",             null: false
  end

  add_index "clans_deletes", ["created_at"], name: "index_clans_deletes_on_created_at", using: :btree

  create_table "clans_leader_changes", id: false, force: true do |t|
    t.integer  "clan_id",       default: 0,     null: false
    t.integer  "world_id",      default: 0,     null: false
    t.integer  "leader_id_old"
    t.integer  "leader_id_new"
    t.datetime "created_at",                    null: false
    t.boolean  "deleted",       default: false, null: false
  end

  add_index "clans_leader_changes", ["created_at"], name: "index_clans_leader_changes_on_created_at", using: :btree

  create_table "clans_name_changes", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.string   "name_old"
    t.string   "name_new"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  add_index "clans_name_changes", ["created_at"], name: "index_clans_name_changes_on_created_at", using: :btree

  create_table "clans_news", id: false, force: true do |t|
    t.integer  "clan_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.string   "tag"
    t.datetime "created_at",             null: false
  end

  add_index "clans_news", ["created_at"], name: "index_clans_news_on_created_at", using: :btree

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

  add_index "clans_tag_changes", ["created_at"], name: "index_clans_tag_changes_on_created_at", using: :btree

  create_table "drops_npcs", id: false, force: true do |t|
    t.integer  "drop_id",               default: 0,   null: false
    t.integer  "npc_id",                default: 0,   null: false
    t.float    "chance",     limit: 24, default: 0.0
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "dumps", force: true do |t|
    t.boolean "public", default: true
    t.string  "name"
    t.string  "path"
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

  create_table "items", force: true do |t|
    t.string   "name"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "items_npcs", id: false, force: true do |t|
    t.integer  "item_id",    default: 0, null: false
    t.integer  "npc_id",     default: 0, null: false
    t.integer  "member_id",  default: 0, null: false
    t.integer  "count"
    t.integer  "action",     default: 0, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "items_places", id: false, force: true do |t|
    t.integer  "item_id",    default: 0, null: false
    t.integer  "pos_x",      default: 0, null: false
    t.integer  "pos_y",      default: 0, null: false
    t.integer  "count"
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
    t.string   "name",                                null: false
    t.integer  "roles",                  default: 0,  null: false
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "email",                  default: "", null: false
    t.string   "encrypted_password",     default: "", null: false
    t.string   "reset_password_token"
    t.datetime "reset_password_sent_at"
    t.datetime "remember_created_at"
    t.integer  "sign_in_count",          default: 0,  null: false
    t.datetime "current_sign_in_at"
    t.datetime "last_sign_in_at"
    t.string   "current_sign_in_ip"
    t.string   "last_sign_in_ip"
    t.string   "confirmation_token"
    t.datetime "confirmed_at"
    t.datetime "confirmation_sent_at"
    t.string   "unconfirmed_email"
    t.integer  "failed_attempts",        default: 0,  null: false
    t.string   "unlock_token"
    t.datetime "locked_at"
    t.string   "authenticity_token"
  end

  add_index "members", ["email"], name: "index_members_on_email", unique: true, using: :btree
  add_index "members", ["reset_password_token"], name: "index_members_on_reset_password_token", unique: true, using: :btree

  create_table "news", force: true do |t|
    t.string   "heading"
    t.text     "content"
    t.integer  "member_id"
    t.datetime "publish_at"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "notifies", force: true do |t|
    t.string   "class_name"
    t.string   "sender"
    t.string   "text"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "notifies_readers", id: false, force: true do |t|
    t.integer "notify_id"
    t.integer "reader_id"
    t.integer "world_id"
  end

  create_table "npcs", force: true do |t|
    t.string   "name"
    t.text     "description"
    t.integer  "strength"
    t.integer  "live"
    t.integer  "pos_x",       default: -10, null: false
    t.integer  "pos_y",       default: -9,  null: false
    t.integer  "unique_npc",  default: 0,   null: false
    t.integer  "flags",       default: 0,   null: false
    t.datetime "created_at"
    t.datetime "updated_at"
    t.integer  "gold"
  end

  add_index "npcs", ["pos_x", "pos_y"], name: "index_npcs_on_pos_x_and_pos_y", using: :btree

  create_table "npcs_members", id: false, force: true do |t|
    t.integer  "npc_id"
    t.integer  "member_id"
    t.integer  "chasecount", default: 0, null: false
    t.integer  "killcount",  default: 0, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "npcs_members", ["npc_id", "member_id"], name: "index_npcs_members_on_npc_id_and_member_id", unique: true, using: :btree

  create_table "other_changes", id: false, force: true do |t|
    t.integer  "user_id",    null: false
    t.integer  "world_id",   null: false
    t.string   "old"
    t.string   "new"
    t.integer  "type",       null: false
    t.datetime "created_at", null: false
  end

  create_table "places", force: true do |t|
    t.string   "name"
    t.text     "desc"
    t.string   "gfx"
    t.integer  "pos_x"
    t.integer  "pos_y"
    t.integer  "flags",      default: 0, null: false
    t.integer  "area_id"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "places", ["area_id"], name: "index_places_on_area_id", using: :btree
  add_index "places", ["pos_x", "pos_y"], name: "index_places_on_pos_x_and_pos_y", unique: true, using: :btree

  create_table "places_nodes", id: false, force: true do |t|
    t.integer  "entry_pos_x"
    t.integer  "entry_pos_y"
    t.integer  "exit_pos_x"
    t.integer  "exit_pos_y"
    t.string   "via"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "places_nodes", ["entry_pos_x", "entry_pos_y", "exit_pos_x", "exit_pos_y"], name: "unique_node", unique: true, using: :btree
  add_index "places_nodes", ["entry_pos_x", "entry_pos_y"], name: "by_entry", using: :btree
  add_index "places_nodes", ["exit_pos_x", "exit_pos_y"], name: "by_exit", using: :btree

  create_table "races", force: true do |t|
    t.string   "name"
    t.integer  "base_live"
    t.integer  "base_strength"
    t.integer  "base_intelligence"
    t.integer  "place_id"
    t.integer  "flags"
    t.datetime "created_at"
    t.datetime "updated_at"
    t.string   "short"
  end

  create_table "railpatterns", force: true do |t|
    t.string   "name"
    t.text     "desc"
    t.string   "gfx"
    t.integer  "cost"
    t.string   "type",       default: "Railpattern"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "readers", force: true do |t|
    t.string   "email",      null: false
    t.string   "name",       null: false
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

  add_index "users_achievements_changes", ["created_at"], name: "index_users_achievements_changes_on_created_at", using: :btree

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

  add_index "users_clan_changes", ["clan_id_new"], name: "index_users_clan_changes_on_clan_id_new", using: :btree
  add_index "users_clan_changes", ["clan_id_old"], name: "index_users_clan_changes_on_clan_id_old", using: :btree
  add_index "users_clan_changes", ["created_at"], name: "index_users_clan_changes_on_created_at", using: :btree

  create_table "users_deletes", id: false, force: true do |t|
    t.integer  "user_id",    default: 0, null: false
    t.integer  "world_id",   default: 0, null: false
    t.string   "name"
    t.datetime "created_at",             null: false
  end

  add_index "users_deletes", ["created_at"], name: "index_users_deletes_on_created_at", using: :btree

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

  add_index "users_name_changes", ["created_at"], name: "index_users_name_changes_on_created_at", using: :btree

  create_table "users_news", id: false, force: true do |t|
    t.integer  "user_id",    default: 0,     null: false
    t.integer  "world_id",   default: 0,     null: false
    t.string   "name"
    t.datetime "created_at",                 null: false
    t.boolean  "deleted",    default: false, null: false
  end

  add_index "users_news", ["created_at"], name: "index_users_news_on_created_at", using: :btree

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

  add_index "users_race_changes", ["created_at"], name: "index_users_race_changes_on_created_at", using: :btree

  create_table "worlds", force: true do |t|
    t.string   "name"
    t.string   "subdomain"
    t.string   "short"
    t.integer  "language_id"
    t.datetime "created_at"
    t.string   "tld"
  end

  create_table "worlds_achievements_changes", id: false, force: true do |t|
    t.integer  "achievement_id", default: 0, null: false
    t.integer  "world_id",       default: 0, null: false
    t.integer  "progress"
    t.datetime "created_at",                 null: false
  end

  add_index "worlds_achievements_changes", ["created_at"], name: "index_worlds_achievements_changes_on_created_at", using: :btree

end
