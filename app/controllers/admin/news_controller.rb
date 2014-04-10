class Admin::NewsController < Admin::BaseController
  before_action :set_admin_news, only: [:show, :edit, :update, :destroy]

  # GET /admin/news
  def index
    @admin_news = ::News.all
  end

  # GET /admin/news/1
  def show
  end

  # GET /admin/news/new
  def new
    @admin_news = ::News.new
  end

  # GET /admin/news/1/edit
  def edit
  end

  # POST /admin/news
  def create
    @admin_news = ::News.new(admin_news_params)
    @admin_news.author = current_member

    if @admin_news.save
      redirect_to @admin_news, notice: 'News was successfully created.'
    else
      render action: 'new'
    end
  end

  # PATCH/PUT /admin/news/1
  def update
    if @admin_news.update(admin_news_params)
      redirect_to @admin_news, notice: 'News was successfully updated.'
    else
      render action: 'edit'
    end
  end

  # DELETE /admin/news/1
  def destroy
    @admin_news.destroy
    redirect_to admin_news_index_url, notice: 'News was successfully destroyed.'
  end

  private
    # Use callbacks to share common setup or constraints between actions.
    def set_admin_news
      @admin_news = ::News.find(params[:id])
    end

    # Only allow a trusted parameter "white list" through.
    def admin_news_params
      params.require(:news).permit(:heading, :content, :publish_at)
    end
end
