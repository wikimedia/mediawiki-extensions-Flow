Given(/^I am on Flow page$/) do
  visit FlowPage
end

Given(/^I have created a Flow topic$/) do
  step "I am on Flow page"
  step "I create a Title of Flow Topic in Flow new topic"
  step "I create a Body of Flow Topic into Flow body"
  step "I click New topic save"
  step "the Flow page should contain Title of Flow Topic"
end

Given(/^the author link is visible$/) do
    on(FlowPage).author_link_element.when_present.should be_visible
end

Given(/^the talk and contrib links are not visible$/) do
  on(FlowPage) do |page|
    page.talk_link_element.should_not be_visible
    page.contrib_link_element.should_not be_visible
  end
end

When(/^I click the Post Actions link$/) do
  on(FlowPage).post_actions_link_element.when_present.click
end

When(/^I click New topic save$/) do
  on(FlowPage).new_topic_save_element.when_present.click
end

# Same thing as action_menu_permalink_steps' "I click Actions menu for the Topic"
When(/^I click the Topic Actions link$/) do
  on(FlowPage).topic_actions_link_element.when_present.click
end

When(/^I create a (.+) in Flow new topic$/) do |flow_title|
  @automated_test_marker = " browsertest edit"
  on(FlowPage) do |page|
    topic_string = flow_title + @random_string + @automated_test_marker
    page.new_topic_title_element.when_present.click
    page.new_topic_title_element.when_present.send_keys(topic_string)
  end
end

When(/^I create a (.+) into Flow body$/) do |flow_body|
  body_string = flow_body + @random_string + @automated_test_marker
  on(FlowPage).new_topic_body_element.when_present.send_keys(body_string)
end

When(/^I hover over the author link$/) do
  on(FlowPage).author_link_element.hover
end

When(/^I see a flow creator element$/) do
  on(FlowPage).author_link_element.should be_visible
end

Then(/^I do not see an actions link$/) do
   on(FlowPage).actions_link_element.should_not exist
end

Then(/^I do not see a block user link$/) do
   on(FlowPage).block_user_element.should_not exist
end

Then(/^links to talk and contrib should be visible$/) do
  on(FlowPage) do |page|
    page.talk_link_element.should be_visible
    page.contrib_link_element.should be_visible
  end
end

Then(/^the Flow page should contain (.+)$/) do |flow_topic|
  on(FlowPage) do |page|
    page.wait_until(20) do	# 10 seconds wasn't enough on ee-flow...
      # TODO Also match the regexp '[2-9] seconds ago' in case of delays.
      # TODO This should look in the particular topic that was added, not
      # blindly look for text.
      # It could note the ID of the first div with id 'flow-topic-<UUID>'
      # before submitting the new topic post,
      # then afterwards find the flow-topic-<UUID> div *preceding* that and search in there.
      # Or after submit it could look for the Title of Flow Topic, find the
      # flow-topic-container containing that and search in there.
      page.text.include? "1 second ago" or page.text.include? "just now"
    end
    page.flow_body.should match(flow_topic + @random_string + @automated_test_marker)
  end
end

Then(/^I should see a Block User link$/) do
  on(FlowPage).block_user_element.should be_visible
end

Then(/^I should see a Delete button$/) do
  on(FlowPage).delete_button_element.should be_visible
end

Then(/^I should see a Delete topic button$/) do
  on(FlowPage).topic_delete_button_element.when_present.should be_visible
end

Then(/^I should see a Hide button$/) do
  on(FlowPage).hide_button_element.when_present.should be_visible
end

Then(/^I should see a Hide topic button$/) do
  on(FlowPage).topic_hide_button_element.when_present.should be_visible
end

Then(/^I should see a Suppress button$/) do
  on(FlowPage).suppress_button_element.should be_visible
end

Then(/^I should see a Suppress topic button$/) do
  on(FlowPage).topic_suppress_button_element.when_present.should be_visible
end
