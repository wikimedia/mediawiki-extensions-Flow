Given(/^the "(.*?)" page has a new unmoderated topic created by me$/) do |page|
  create_topic_on(page, 'Thank me please!', 'Hello')
end

Given(/^user B exists$/) do
  ensure_account(:b)
end

Given(/^the most recent topic on "(.*?)" is written by user B$/) do |page|
  as_user(:b) { create_topic_on(page, 'Thank me please!', 'Hello') }
end

When(/^I click on the Thank button$/) do
  on(FlowPage).thank_button_element.click
end

When(/^I see a Thank button$/) do
  on(FlowPage).thank_button_element.when_present
end

Then(/^I should not see a Thank button$/) do
  expect(on(FlowPage).thank_button_element).not_to be_visible
end

Then(/^I should see the Thank button be replaced with Thanked button$/) do
  on(FlowPage) do |page|
    expect(page.thanked_button_element.when_present).to be_visible
    expect(page.thank_button_element).not_to be_visible
  end
end
