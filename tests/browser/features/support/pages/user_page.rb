class UserPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("User talk:ENV['MEDIAWIKI_USER']")

  h1(:first_heading, id: "firstHeading")
end
