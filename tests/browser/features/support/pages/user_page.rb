class UserPage
  include PageObject

  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url "User talk:ENV['MEDIAWIKI_USER']"

  h1(:first_heading, id: "firstHeading")
  # on test2wiki, page titles include an additional ShortURL after span containing page title.
  span(:page_title) do |page|
    page.first_heading_element.span_element(index: 0)
  end
end
