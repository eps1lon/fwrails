module DeleteMarkable
  extend ActiveSupport::Concern
  
  module ClassMethods
    # belongs_to relation, :conditions => {:self => {:deleted => false}}
    def on_deleted_nullify_relation(relation)
      alias_name = "__#{relation}"
      alias_method alias_name, relation.to_s
      
      define_method relation do
        self.send(alias_name) if !self.try(:deleted)
      end
    end
  end
end
