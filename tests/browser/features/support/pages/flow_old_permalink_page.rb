class FlowOldPermalinkPage
  include PageObject

  @params = { page: 'Talk:Flow QA', workflow_id: 'no workflow' }
  page_url params[:page] + "?workflow=<%=params[:workflow_id]%>"
end
