class Admin::MembersController < Admin::BaseController
  before_action :set_admin_member, only: [:show, :edit, :update, :destroy]

  # GET /admin/members
  def index
    @admin_members = Member.all
  end

  # GET /admin/members/1
  def show
  end

  # GET /admin/members/new
  def new
    @admin_member = Member.new
  end

  # GET /admin/members/1/edit
  def edit
  end

  # POST /admin/members
  def create
    logger.debug admin_member_params
    @admin_member = Member.new(admin_member_params)
    @admin_member.skip_confirmation!

    if @admin_member.save
      redirect_to [:admin, @admin_member], notice: 'Member was successfully created.'
    else
      render action: 'new'
    end
  end

  # PATCH/PUT /admin/members/1
  def update
    if @admin_member.update(admin_member_params)
      redirect_to [:admin, @admin_member], notice: 'Member was successfully updated.'
    else
      render action: 'edit'
    end
  end

  # DELETE /admin/members/1
  def destroy
    @admin_member.destroy
    redirect_to admin_members_url, notice: 'Member was successfully destroyed.'
  end

  private
    # Use callbacks to share common setup or constraints between actions.
    def set_admin_member
      @admin_member = Member.find(params[:id])
    end

    # Only allow a trusted parameter "white list" through.
    def admin_member_params
      params[:member][:roles] ||= []
      params[:member][:roles].map! &:to_sym
      params.require(:member).permit(:email, :name, :password, :password_confirmation, :roles => [])
    end
end
