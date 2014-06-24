Given(/^I create a Flow topic with title "(.*?)"$/) do |arg1|
  step 'I create a ' + arg1 + ' in Flow new topic'
  step 'I create a Body of Flow Topic into Flow body'
  step 'I click New topic save'
  step 'the top post should have a heading which contains "' + arg1 + '"'
end

When(/^I see a dialog box$/) do
  on(FlowPage).dialog_element.when_present.should be_visible
end

When(/^I give reason for deletion as being "(.*?)"$/) do |arg1|
   on(FlowPage).dialog_input_element.when_present.send_keys( arg1 )
end

When(/^I click Delete topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

Then(/^the top post should be marked as deleted$/) do
  step 'the page renders in 5 seconds'
  on(FlowPage).flow_first_topic_moderation_msg.should match( 'This topic was deleted' )
end

When(/^I give reason for suppression as being "(.*?)"$/) do |arg1|
  on(FlowPage).dialog_input_element.when_present.send_keys( arg1 )
end

When(/^I click Suppress topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

Then(/^the top post should be marked as suppressed$/) do
  step 'the page renders in 5 seconds'
  on(FlowPage).flow_first_topic_moderation_msg.should match( 'This topic was suppressed' )
end

