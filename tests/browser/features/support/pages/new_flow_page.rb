require "page-object"

class NewFlowPage < FlowPage
  # MEDIAWIKI_URL must have User_talk in $wgFlowOccupyNamespaces.
  page_url "User_talk:New page " + Array.new(8) { [*'0'..'9', *'a'..'z', *'A'..'Z'].sample }.join
end
