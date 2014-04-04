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

Given(/^I have created a topic and note the content$/) do
  @random_string2 = Random.new.rand.to_s
  step "I am on Flow page"
  step "I create a Special " + @random_string2 + " in Flow new topic"
  step "I create a Body of Flow Topic into Flow body"
  step "I click New topic save"
  step "the Flow page should contain " + @random_string2
end

Given(/^I have created (.+) topics$/) do |count|
  step "I am on Flow page"
  count.to_i.times do
    step "I create a Title of Flow Topic in Flow new topic"
    step "I create a Body of Flow Topic into Flow body"
    step "I click New topic save"
    step "the Flow page should contain Title of Flow Topic"
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

When(/^I scroll down to the bottom of the page$/) do
  on(FlowPage).flow_body_element.send_keys :end
end

When(/^I refresh the page$/) do
  on(FlowPage).refresh
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
    page.wait_until(20) do
      page.preview_button_element.visible? != true
      page.cancel_button_element.visible? != true
    end
    page.flow_body.should match(flow_topic + @random_string + @automated_test_marker)
  end
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

Then(/^I can keep scrolling down to see older topics$/) do
  step "I can keep scrolling down -1 times to see older topics"
end

Then(/^I can keep scrolling down (.+) times to see older topics$/) do |count|
  # This step keeps scrolling down until no more old topics can be loaded.
  # It asserts that after each loading, more topics are displayed.
  on(FlowPage) do |page|
    topic_count = page.div_elements(class: 'flow-topic-container').size
    count = count.to_i
    # infinite scrolling
    while page.next_page_element.exists? and count != 0
      step "I scroll down to the bottom of the page"
      step "older topics are loaded in 10 seconds"

      # there should be more topics loaded...
      if topic_count >= page.div_elements(class: 'flow-topic-container').size
        raise "New topics are not loaded"
      else
        topic_count = page.div_elements(class: 'flow-topic-container').size
        count -= 1
      end
    end
  end
end

Then (/^older topics are loaded in (.+) seconds$/) do |seconds|
  on(FlowPage).loading_next_page_element.when_present # need sometime for next page to start loading
  on(FlowPage).loading_next_page_element.when_not_present seconds.to_i
end

Then(/^I do not see the first topic$/) do
  on(FlowPage).flow_body.should_not match(@random_string2)
end

Then(/^I see the first topic$/) do
  on(FlowPage).flow_body.should match(@random_string2)
end
