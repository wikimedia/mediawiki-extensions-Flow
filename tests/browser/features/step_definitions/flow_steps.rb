Given(/^I am on Flow page$/) do
  visit FlowPage
end

When(/^I create a (.+) in Flow new topic$/) do |flow_title|
  @automated_test_marker = ' browsertest edit'
  on(FlowPage) do |page|
    page.new_topic_title_element.when_present.click
    page.new_topic_title_element.when_present.send_keys(flow_title + @random_string + @automated_test_marker)
  end
end

When(/^I create a (.+) into VisualEditor Flow body$/) do |flow_body|
  on(FlowPage).new_topic_body_ve_element.when_present.send_keys(flow_body + @random_string + @automated_test_marker)
end

When(/^I create a (.+) into Flow body$/) do |flow_body|
  on(FlowPage).new_topic_body_element.when_present.send_keys(flow_body + @random_string + @automated_test_marker)
end

When(/^I click New topic save$/) do
  on(FlowPage).new_topic_save_element.when_present.click
end

Then(/^the Flow page should contain (.+)$/) do |flow_topic|
  on(FlowPage) do |page|
    page.wait_until(20) do	# 10 seconds wasn't enough on ee-flow...
      page.text.include? 'just now'
    end
    page.flow_body.should match(flow_topic + @random_string + @automated_test_marker)
  end
end

Given(/^I have created a Flow topic$/) do
  step 'I am on Flow page'
  step 'I create a Title of Flow Topic in Flow new topic'
  step 'I create a Body of Flow Topic into Flow body'
  step 'I click New topic save'
  step 'the Flow page should contain Title of Flow Topic'
end

Given(/^I have created a VisualEditor Flow topic$/) do
  step 'I am on Flow page'
  step 'I create a Title of Flow Topic in Flow new topic'
  step 'I create a Body of Flow Topic into VisualEditor Flow body'
  step 'I click New topic save'
  step 'the Flow page should contain Title of Flow Topic'
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

When(/^I hover over the author link$/) do
  on(FlowPage).author_link_element.fire_event('onmouseover')
end

Then(/^links to talk and contrib should be visible$/) do
  on(FlowPage) do |page|
    page.talk_link_element.should be_visible
    page.contrib_link_element.should be_visible
  end
end
