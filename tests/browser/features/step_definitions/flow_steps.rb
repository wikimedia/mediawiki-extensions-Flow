Given(/^I am on Flow page$/) do
  visit FlowPage
  step "The Flow page is fully loaded"
  step "page has no ResourceLoader errors"
end

Given(/^The Flow page is fully loaded$/ ) do
  on(FlowPage).new_topic_body_element.when_not_visible
end

Then(/^I am on my user page$/) do
  # Get the title of the page without '_' characters
  text = 'User:' + ENV["MEDIAWIKI_USER"].gsub(/_/, ' ')
  on(UserPage).page_title_element.text.should == text
end

Given(/^I am on a new board$/) do
  visit NewFlowPage
end

# @todo: Rewrite to use more generic step below
Given(/^I have created a Flow topic$/) do
  step "I have created a Flow topic with title \"Title of Flow topic\""
end

Given(/^I have created a Flow topic with title "(.+)"$/) do |title|
  step "I am on Flow page"
  step "I type \"" + title + "\" into the new topic title field"
  step "I type \"" + title + "\" into the new topic content field"
  step "I click New topic save"
end

Given(/^the author link is visible$/) do
    on(FlowPage).author_link_element.when_present.should be_visible
end

When(/^I am viewing Topic page$/) do
  on(FlowPage).wait_until { @browser.url =~ /Topic/ }
end

When(/^I click the Post Actions link$/) do
  on(FlowPage).post_actions_link_element.when_present.click
end

When(/^I click New topic save$/) do
  on(FlowPage) do |page|
    page.new_topic_save_element.when_present.click

    # Wait for the save to finish, at which point the button will hide
    page.new_topic_save_element.when_not_visible(10); # Bug 71476 - Saving a new topic can take >5s on beta labs
  end
end

Then(/^the Save New Topic button should be disabled$/) do
  val = on(FlowPage).new_topic_save_element.attribute( "disabled" )
  expect(val).to eq("true")
end

When(/^I click the Topic Actions link$/) do
  on(FlowPage).topic_actions_link_element.when_present.click
end

When(/^I type "(.+)" into the new topic title field$/) do |flow_title|
  @automated_test_marker = " browsertest edit"
  on(FlowPage) do |page|
    topic_string = flow_title + @random_string + @automated_test_marker
    page.new_topic_title_element.when_present.click
    page.new_topic_title_element.when_present.focus
    page.new_topic_title_element.when_present.send_keys(topic_string)
  end
end

When(/^I type "(.+)" into the new topic content field$/) do |flow_body|
  body_string = flow_body + @random_string + @automated_test_marker
  on(FlowPage).new_topic_body_element.when_present.send_keys(body_string)
end

When(/^I hover over the author link$/) do
  on(FlowPage).author_link_element.hover
end

When(/^I see a flow creator element$/) do
  on(FlowPage).author_link_element.should be_visible
end

When(/^I click the flow creator element$/) do
  on(FlowPage).author_link_element.click
end

Then(/^the block author link does not exist$/) do
   on(FlowPage).usertools_block_user_link_element.should_not exist
end

Then(/^I do not see an actions link$/) do
   on(FlowPage).actions_link_element.should_not exist
end

Given(/^the talk to author link is not visible$/) do
  on(FlowPage).usertools_talk_link_element.should_not be_visible
end

Given(/^the block author link is not visible$/) do
  on(FlowPage).usertools_block_user_link_element.should_not be_visible
end

Then(/^the talk to author link is visible$/) do
  on(FlowPage).usertools_talk_link_element.when_present.should be_visible
end

Then(/^the block author link is visible$/) do
  on(FlowPage).usertools_block_user_link_element.when_present.should be_visible
end

Then(/^the preview and cancel buttons have disappeared$/) do
  on(FlowPage) do |page|
    page.wait_until(20) do
      page.preview_button_element.visible? != true &&
      page.cancel_button_element.visible? != true
    end
  end
end

Then(/^the top post should not have a heading which contains "(.+)"$/) do |text|
  on(FlowPage).flow_first_topic_heading.should_not match(text)
end

Then(/^the top post should have a heading which contains "(.+)"$/) do |text|
  on(FlowPage) do |page|
    page.flow_first_topic_heading_element.when_present
    page.flow_first_topic_heading.should match(text)
  end
end

Then(/^the top post should have content which contains "(.+)"$/) do |text|
  step 'the preview and cancel buttons have disappeared'
  on(FlowPage).flow_first_topic_body.should match(text)
end

Then(/^the content of the top post should be visible$/) do
  on(FlowPage).flow_first_topic_body_element.when_present.should be_visible
end

Then(/^the content of the top post should not be visible$/) do
  on(FlowPage).flow_first_topic_body_element.should_not be_visible
end

Then(/^I should see a Delete button$/) do
  on(FlowPage).delete_button_element.should be_visible
end

Then(/^I should see a Delete topic button$/) do
  on(FlowPage).topic_delete_button_element.when_present.should be_visible
end

When(/^I click the Delete topic button$/) do
  on(FlowPage).topic_delete_button_element.when_present.click
end

Then(/^I should see a Hide button$/) do
  on(FlowPage).hide_button_element.when_present.should be_visible
end

Then(/^I should see a Hide topic button$/) do
  on(FlowPage).topic_hide_button_element.when_present.should be_visible
end

When(/^I click the Hide topic button$/) do
  on(FlowPage).topic_hide_button_element.when_present.click
end

Then(/^I should see a Suppress button$/) do
  on(FlowPage).suppress_button_element.should be_visible
end

Then(/^I should see a Suppress topic button$/) do
  on(FlowPage).topic_suppress_button_element.when_present.should be_visible
end

When(/^I click the Suppress topic button$/) do
  on(FlowPage).topic_suppress_button_element.when_present.click
end
