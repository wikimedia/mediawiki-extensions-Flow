class FlowOldPermalinkPage
  include PageObject
  include URL

  @params = { page: 'Talk:Flow QA', workflow_id: 'no workflow' }
  page_url URL.url(params[:page]) + "?workflow=<%=params[:workflow_id]%>"
end
