require "page-object"

class NewFlowPage < FlowPage
  include URL
  # MEDIAWIKI_URL must have Flow_test_talk in $wgFlowOccupyNamespaces.
  page_url URL.url("Flow_test_talk:New page " + Random.srand.to_s)
end
