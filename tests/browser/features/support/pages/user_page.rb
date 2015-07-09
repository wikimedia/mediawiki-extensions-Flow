class UserPage
  include PageObject

  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url "User talk:user"

  h1(:first_heading, id: "firstHeading")
end
