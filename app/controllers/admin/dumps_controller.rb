class Admin::DumpsController < Admin::BaseController
  before_action :set_admin_dump, only: [:show, :edit, :update, :destroy]

  # GET /admin/dumps
  def index
    @admin_dumps = ::Dump.all
  end

  # GET /admin/dumps/1
  def show
  end

  # GET /admin/dumps/new
  def new
    @admin_dump = ::Dump.new
  end

  # GET /admin/dumps/1/edit
  def edit
  end

  # POST /admin/dumps
  def create
    @admin_dump = ::Dump.new(admin_dump_params)

    if @admin_dump.save
      redirect_to [:admin, @admin_dump], notice: 'Dump was successfully created.'
    else
      render action: 'new'
    end
  end

  # PATCH/PUT /admin/dumps/1
  def update
    if @admin_dump.update(admin_dump_params)
      redirect_to [:admin, @admin_dump], notice: 'Dump was successfully updated.'
    else
      render action: 'edit'
    end
  end

  # DELETE /admin/dumps/1
  def destroy
    @admin_dump.destroy
    redirect_to admin_dumps_url, notice: 'Dump was successfully destroyed.'
  end

  private
    # Use callbacks to share common setup or constraints between actions.
    def set_admin_dump
      @admin_dump = ::Dump.find(params[:id])
    end

    # Only allow a trusted parameter "white list" through.
    def admin_dump_params
      params.require(:dump).permit(:name, :path, :public)
    end
end
