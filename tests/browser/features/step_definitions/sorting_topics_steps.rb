When(/^I sort by Newest topics$/) do
  on(FlowPage) do |page|
    page.sorting_element.when_present
    page.recently_active_topics_link_element.when_present.click
    page.newest_topics_choice_element.when_present.click
    page.sorting_element.when_not_present
  end
end

Then(/^it is sorted by Newest topics$/) do
  on(FlowPage) do |page|
    page.newest_topics_link_element.when_present
  end
end

When(/^I sort by Recently active topics$/) do
  on(FlowPage) do |page|
    page.sorting_element.when_present
    page.newest_topics_link_element.when_present.click
    page.recently_active_topics_choice_element.when_present.click
    page.sorting_element.when_not_present
  end
end

Then(/^it is sorted by Recently active topics$/) do
  on(FlowPage) do |page|
    page.recently_active_topics_link_element.when_present
  end
end
