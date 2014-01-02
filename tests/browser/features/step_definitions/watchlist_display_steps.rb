Given(/^Watchlist is checked$/) do
  on(FlowPage).unwatch_element.when_present.should be_visible
end

When(/^I navigate to the Watchlist$/) do
  on(FlowPage).watchlist_link_element.when_present.click
end

Then(/^the Watchlist page has a link to the Flow page$/) do
  on(WatchListPage).flow_link_element.when_present.should be_visible
end

