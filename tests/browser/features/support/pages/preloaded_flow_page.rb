class PreloadedFlowPage
  include PageObject

  page_url "Talk:Flow_QA?topiclist_preloadtitle=<%= params[:topiclist_preloadtitle] %>&topiclist_preload=<%= params[:topiclist_preload] %>"
end
