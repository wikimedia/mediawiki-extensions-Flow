require "page-object"

class NewFlowPage < AbstractFlowPage
  # MEDIAWIKI_URL must have Flow_test_talk in $wgFlowOccupyNamespaces.
  page_url "Flow_test_talk:New_page_" + Random.srand.to_s
end
