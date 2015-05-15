module InvertScope
  extend ActiveSupport::Concern
  
  included do
    # inverts the where conditions of a scope
    scope :invert, ->(scope_name) { where(unscoped.send(scope_name).where_values.reduce(:and).not) }
  end
end