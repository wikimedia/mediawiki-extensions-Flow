Given(/^I am not watching the Flow board$/) do
  on(FlowPage) do |page|
    page.board_unwatch_link_element.when_present.click unless page.board_watch_link_element.visible?
  end
end

Given(/^I am not watching the Flow topic$/) do
  on(FlowPage).first_topic_unwatch_link_element.when_present.click
end

Given(/^I am watching the Flow topic$/) do
  on(FlowPage).first_topic_unwatch_link_element.when_present
end

Given(/^I am watching the Flow board$/) do
  on(FlowPage) do |page|
    page.board_watch_link_element.when_present.click unless page.board_unwatch_link_element.visible?
  end
end

When(/^I click the Unwatch Board link$/) do
  on(FlowPage).board_unwatch_link_element.when_present.click
end

When(/^I click the Unwatch Topic link$/) do
  on(FlowPage).first_topic_unwatch_link_element.when_present.click
end

When(/^I click the Watch Board link$/) do
  on(FlowPage).board_watch_link_element.when_present.click
end

When(/^I click the Watch Topic link$/) do
  on(FlowPage).first_topic_watch_link_element.when_present.click
end

Then(/^I should see the Unwatch Topic link$/) do
  expect(on(FlowPage).first_topic_unwatch_link_element.when_present).to be_visible
end

Then(/^I should not see any watch links$/) do
  on(FlowPage) do |page|
    expect(page.board_watch_link_element).not_to be_visible
    expect(page.first_topic_watch_link_element).not_to be_visible
  end
end

Then(/^I should see the Unwatch Board link$/) do
  on(FlowPage) do |page|
    page.board_unwatch_link_element.when_present
  end
end

Then(/^I should see the Watch Board link$/) do
  expect(on(FlowPage).board_watch_link_element.when_present).to be_visible
end

Then(/^I should see the Watch Topic link$/) do
  on(FlowPage) do |page|
    page.first_topic_watch_link_element.when_present
  end
end
