require "page-object"

class WatchListPage
  include PageObject

  li(:flow_link, class: "flow-recentchanges-line")
end
