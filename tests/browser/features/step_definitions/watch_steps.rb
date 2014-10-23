Given(/^I am not watching the Flow topic$/) do
  on(FlowPage).first_topic_unwatch_link_element.when_present.click
end

When(/^I click the Watch Topic link$/) do
  on(FlowPage).first_topic_watch_link_element.when_present.click
end

Then(/^I should see the Unwatch Topic link$/) do
  expect(on(FlowPage).first_topic_unwatch_link_element.when_present).to be_visible
end

Given(/^I am watching the Flow topic$/) do
  pending # express the regexp above with the code you wish you had
end

When(/^I click the Unwatch Topic link$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see the Watch Topic link$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I am not watching the Flow board$/) do
  pending # express the regexp above with the code you wish you had
end

When(/^I click the Watch Board link$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see the Unwatch Board link$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I am watching the Flow board$/) do
  pending # express the regexp above with the code you wish you had
end

When(/^I click the Unwatch Board link$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see the Watch Board link$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should not see any watch links$/) do
  pending # express the regexp above with the code you wish you had
end
