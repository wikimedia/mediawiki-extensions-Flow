
class NewFlowPage < AbstractFlowPage
  include PageObject

  # MEDIAWIKI_URL must have Flow_test_talk as Flow in $wgNamespaceContentModels.
  page_url "./Flow_test_talk:New_page_<%= params[:pagetitle] ? params[:pagetitle] : Random.srand.to_s %>"
end
