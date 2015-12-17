Given(/^I am on a new board$/) do
  visit NewFlowPage
  step 'The Flow page is fully loaded'
  step 'page has no ResourceLoader errors'
end

Given(/^I am on Flow page$/) do
  visit FlowPage
  step 'The Flow page is fully loaded'
  step 'page has no ResourceLoader errors'
end

# @todo: Rewrite to use more generic step below
Given(/^I have created a Flow topic$/) do
  step "I have created a Flow topic with title \"Title of Flow topic\""
end

Given(/^I have created a Flow topic with title "(.+)"$/) do |title|
  step "I am on Flow page"
  step "I type \"#{title}\" into the new topic title field"
  step "I type \"#{title}\" into the new topic content field"
  step "I click New topic save"
  step "topic \"#{title}\" is saved"
end

Given(/^the author link is visible$/) do
  on(FlowPage).author_link_element.when_present.when_present
end

Given(/^the block author link is not visible$/) do
  on(FlowPage).usertools_block_user_link_element.when_not_visible
end

Given(/^The Flow page is fully loaded$/) do
  on(FlowPage) do |page|
    page.new_topic_link_element.when_not_visible
    page.overlay_element.when_not_visible
  end
end

Given(/^the talk to author link is not visible$/) do
  on(FlowPage).usertools_talk_link_element.when_not_visible
end

When(/^I am viewing Topic page$/) do
  on(FlowPage).wait_until { @browser.url =~ /Topic/ }
end

When(/^I click New topic save$/) do
  on(FlowPage) do |page|
    page.new_topic_save_element.when_present.click
  end
end

# This will only work for titles without wikitext
# due to topic_with_title
When(/^topic "(.+)" is saved$/) do |title|
  on(FlowPage) do |page|
    page.new_topic_save_element.when_not_visible(10)

    full_title = @data_manager.get title
    page.topic_with_title(full_title).when_present
  end
end

When(/^I select the Delete topic button$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_delete_button_element
    page.select_menu_option menu, option
  end
end

When(/^I click the flow creator element$/) do
  on(FlowPage).author_link_element.click
end

When(/^I click the Hide topic button$/) do
  on(FlowPage).topic_hide_button_element.when_present.click
end

When(/^I hover over the author link$/) do
  on(FlowPage).author_link_element.hover
end

When(/^I see a flow creator element$/) do
  on(FlowPage).author_link_element.should be_visible
end

When(/^I type "(.+)" into the new topic content field$/) do |flow_body|
  body_string = @data_manager.get flow_body
  on(FlowPage).new_topic_body_element.when_present.send_keys(body_string)
end

When(/^I type "(.+)" into the new topic title field$/) do |flow_title|
  on(FlowPage) do |page|
    @topic_string = @data_manager.get flow_title
    page.new_topic_title_element.when_present.click
    page.new_topic_title_element.when_present.focus
    page.new_topic_title_element.when_present.send_keys(@topic_string)
  end
end

When(/I log out/) do
  on(FlowPage) do |page|
    page.logout
    page.logout_element.when_not_visible
  end
end

When(/^I visit the board history page$/) do
  visit BoardHistoryPage
  on(BoardHistoryPage).flow_board_history_element.when_present
end

When(/^I visit the topic history page$/) do
  step 'I select History from the Actions menu'
  on(TopicHistoryPage).flow_topic_history_element.when_present
end

When(/^I select History from the Actions menu$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_history_button_element
    page.select_menu_option menu, option
  end
end

Then(/^I am on my user page$/) do
  text = "User:#{user_label}"
  expect(on(UserPage).first_heading_element.when_present.text).to match(text)
end

Then(/^I should see a Delete button$/) do
  expect(on(FlowPage).delete_button_element).to be_visible
end

Then(/^I should see a Delete topic button$/) do
  expect(on(FlowPage).topic_delete_button_element.when_present).to be_visible
end

Then(/^I should see a Hide button$/) do
  expect(on(FlowPage).hide_button_element.when_present).to be_visible
end

Then(/^I should see a Hide topic button$/) do
  expect(on(FlowPage).topic_hide_button_element.when_present).to be_visible
end

Then(/^I should see a Suppress button$/) do
  expect(on(FlowPage).suppress_button_element).to be_visible
end

Then(/^I should see a Suppress topic button$/) do
  expect(on(FlowPage).topic_suppress_button_element.when_present).to be_visible
end

Then(/^the block author link should not be visible$/) do
  expect(on(FlowPage).usertools_block_user_link_element).not_to be_visible
end

Then(/^the block author link should be visible$/) do
  expect(on(FlowPage).usertools_block_user_link_element.when_present).to be_visible
end

Then(/^the content of the top post should be visible$/) do
  expect(on(FlowPage).flow_first_topic_body_element.when_present).to be_visible
end

Then(/^the content of the top post should not be visible$/) do
  expect(on(FlowPage).flow_first_topic_body_element).not_to be_visible
end

Then(/^the Save New Topic button should be disabled$/) do
  val = on(FlowPage).new_topic_save_element.attribute("disabled")
  expect(val).to eq("true")
end

Then(/^the talk to author link should be visible$/) do
  expect(on(FlowPage).usertools_talk_link_element.when_present).to be_visible
end

Then(/^the top post should have a heading which contains "(.+)"$/) do |text|
  on(FlowPage) do |page|
    page.wait_until do
      actual_text = page.flow_first_topic_heading_element.when_present.text
      actual_text.match text
    end
  end
end

Then(/^the top post should have content which contains "(.+)"$/) do |text|
  expect(on(FlowPage).flow_first_topic_body).to match(text)
end

Then(/^the top post should not have a heading which contains "(.+)"$/) do |text|
  expect(on(FlowPage).flow_first_topic_heading).not_to match(text)
end

Then(/^I see the topic "(.*?)" on the board$/) do |title|
  full_title = @data_manager.get title
  on(FlowPage).topic_with_title(full_title).when_present
end

Then(/^everybody sees the topic "(.*?)" on the board$/) do |title|
  step 'I log out'
  step 'I am on Flow page'
  step "I see the topic \"#{title}\" on the board"
end

Then(/^I see the following entries in board history$/) do |table|
  on(BoardHistoryPage) do |page|
    table.hashes.each do |row|
      action = row['action']
      topic = @data_manager.get row['topic']
      entry = %(#{action} "#{topic}")
      expect(page.flow_board_history).to match(entry)
    end
  end
end

Then(/^I see the following entries in topic history$/) do |table|
  on(TopicHistoryPage) do |page|
    table.hashes.each do |row|
      action = row['action']
      topic = @data_manager.get row['topic']
      entry = %(#{action} "#{topic}")
      expect(page.flow_topic_history).to match(entry)
    end
  end
end

Then(/^I do not see the following entries in board history$/) do |table|
  on(BoardHistoryPage) do |page|
    table.hashes.each do |row|
      action = row['action']
      topic = @data_manager.get row['topic']
      entry = %(#{action} "#{topic}")
      expect(page.flow_board_history).to_not match(entry)
    end
  end
end

Then(/^I do not see the following entries in topic history$/) do |table|
  on(TopicHistoryPage) do |page|
    table.hashes.each do |row|
      action = row['action']
      topic = @data_manager.get row['topic']
      entry = %(#{action} "#{topic}")
      expect(page.flow_topic_history).to_not match(entry)
    end
  end
end
