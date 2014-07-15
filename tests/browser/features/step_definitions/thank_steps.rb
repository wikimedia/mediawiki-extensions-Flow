require "mediawiki_api"

Given(/^the "(.*?)" page has a new unmoderated topic$/) do |title|
  client = on(APIPage).client
  client.log_in(ENV["MEDIAWIKI_USER"], ENV["MEDIAWIKI_PASSWORD"])
  client.action( 'flow', token_type: 'edit', submodule: 'new-topic', page: title, nttopic:'Thank me please!', ntcontent: 'Hello' )
end

Given(/^the "(.*?)" page has a new unmoderated topic created by me$/) do |title|
  step 'the "Talk:Flow QA" page has a new unmoderated topic'
end

Given(/^the most recent topic on "(.*?)" is written by another user$/) do |title|
  client = on(APIPage).client
  username = 'Selenium Flow user 2'
  begin
    client.create_account(username, ENV["MEDIAWIKI_PASSWORD"])
  rescue MediawikiApi::ApiError
    puts 'Assuming user ' + username + ' already exists since was unable to create.'
  end

  client.log_in(username, ENV["MEDIAWIKI_PASSWORD"])
  client.action( 'flow', token_type: 'edit', submodule: 'new-topic', page: title, nttopic:'Thank me please!', ntcontent: 'Hello' )
end

Then(/^I do not see a Thank button$/) do
  on(FlowPage).thank_button_element.should_not exist
end

Then(/^I do not see a Thanked button$/) do
  on(FlowPage).thanked_button_element.should_not exist
end

Then(/^I should see the Thank button be replaced with Thanked button$/) do
  # TODO: If we decide to use a loading indicator for the thank button,
  # then condition can be changed to loading_indicator.when_not_present.
  @target_container.span_element(class: 'mw-thanks-flow-thanked').when_visible
end

When(/^I see a Thank button$/) do
  # Carry on with the test per normal if the element is present.
  on(FlowPage).thank_button_element.should be_visible
  @target_container = on(FlowPage).thank_button_element.parent
end

When(/^I click on the Thank button$/) do
  on(FlowPage).thank_button_element.click
end

Then(/^I should not see the Thank button for that post$/) do
  on(FlowPage).thank_button_element.should_not exist
end
