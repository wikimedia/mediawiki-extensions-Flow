Given(/^I am on a Flow page with a deleted post with heading "(.*?)"$/) do |arg1|
  step 'I am logged in'
  step 'I am on Flow page'
  step 'I have created a Flow topic with title "' + arg1 + '"'
  step 'the top post should have a heading which contains "' + arg1 + '"'
  step 'I click the Topic Actions link'
  step 'I click the Delete topic button'
  step 'I see a dialog box'
  step 'I give reason for deletion as being "DeletedPost"'
  step 'I click Delete topic'
  step 'the top post should be marked as deleted'
end

Given(/^I am anonymous$/) do
  on(FlowPage).logout_element.click
  visit FlowPage
end

# TODO (mattflaschen, 2014-06-25): Have the below actions (e.g. 'I
# click Delete topic') wait for the dialog box to be non-visible
# afterwards (to confirm API call finished), rather than use
# timeouts?
When(/^I see a dialog box$/) do
  on(FlowPage).dialog_element.when_present.should be_visible
end

When(/^I give reason for hiding as being "(.*?)"$/) do |arg1|
   on(FlowPage).dialog_input_element.when_present.send_keys( arg1 )
end

When(/^I click Hide topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

When(/^I give reason for deletion as being "(.*?)"$/) do |arg1|
   on(FlowPage).dialog_input_element.when_present.send_keys( arg1 )
end

When(/^I click Delete topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

Then(/^the top post should be marked as deleted$/) do
  step 'the page has re-rendered'
  on(FlowPage).flow_first_topic_moderation_msg.should match( 'This topic was deleted' )
end

When(/^I give reason for suppression as being "(.*?)"$/) do |arg1|
  on(FlowPage).dialog_input_element.when_present.send_keys( arg1 )
end

When(/^I click Suppress topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

Then(/^the top post should be marked as suppressed$/) do
  step 'the page has re-rendered'
  on(FlowPage).flow_first_topic_moderation_msg.should match( 'This topic was suppressed' )
end

When(/^I cancel the dialog$/) do
  on(FlowPage).dialog_cancel_element.when_present.click
end

Then(/^I do not see the dialog box$/) do
  on(FlowPage).dialog_element.when_not_present
end

Then(/^I confirm$/) do
  on(FlowPage).confirm(true){}
end
