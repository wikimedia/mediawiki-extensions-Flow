require_relative 'wiki_page'

class NewWikiPage < WikiPage
  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("<%=params[:page]%>")
end
