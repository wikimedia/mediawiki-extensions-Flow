require "page-object"

class NewFlowPage < FlowPage

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:New page " + Array.new(8){[*'0'..'9', *'a'..'z', *'A'..'Z'].sample}.join )
end
