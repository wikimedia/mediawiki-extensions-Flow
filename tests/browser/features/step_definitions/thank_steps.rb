Given(/^the "(.*?)" page has a new unmoderated topic created by me$/) do |title|
  client = on(APIPage).client
  client.log_in(ENV["MEDIAWIKI_USER"], ENV["MEDIAWIKI_PASSWORD"])
  client.action('flow', token_type: 'edit', submodule: 'new-topic', page: title, nttopic: 'Thank me please!', ntcontent: 'Hello')
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
  client.action('flow', token_type: 'edit', submodule: 'new-topic', page: title, nttopic: 'Thank me please!', ntcontent: 'Hello')
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
