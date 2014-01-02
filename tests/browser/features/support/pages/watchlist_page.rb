require "page-object"

class WatchListPage
  include PageObject

  list_item(:flow_link, class: "flow-recentchanges-line")
end
