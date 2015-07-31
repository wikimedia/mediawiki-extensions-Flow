require_relative 'wiki_page'

class NewWikiPage < WikiPage
  # MEDIAWIKI_URL must have this in $wgFlowOccupyNamespaces.
  page_url "<%=params[:page]%>"
end
