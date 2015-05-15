module NpcsHelper
  def position_text(npc)
    super(npc.pos_x, npc.pos_y)
  end
end
