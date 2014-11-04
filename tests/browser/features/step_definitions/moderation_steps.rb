When(/^I cancel the dialog$/) do
  on(FlowPage).dialog_cancel_element.when_present.click
end

When(/^I click Delete topic$/) do
  on(FlowPage).dialog_submit_delete_element.when_present.click
end

When(/^I click Hide topic$/) do
  on(FlowPage).dialog_submit_hide_element.when_present.click
end

When(/^I click Suppress topic$/) do
  on(FlowPage).dialog_submit_suppress_element.when_present.click
end

When(/^I give reason for deletion as being "(.*?)"$/) do |delete_reason|
  on(FlowPage).dialog_input_element.when_present.send_keys(delete_reason)
end

When(/^I give reason for hiding as being "(.*?)"$/) do |hide_reason|
  on(FlowPage).dialog_input_element.when_present.send_keys(hide_reason)
end

When(/^I give reason for suppression as being "(.*?)"$/) do |suppress_reason|
  on(FlowPage).dialog_input_element.when_present.send_keys(suppress_reason)
end

When(/^I see a dialog box$/) do
  on(FlowPage).dialog_element.when_present
end

Then(/^I confirm$/) do
  on(FlowPage).confirm(true){}
end

Then(/^I do not see the dialog box$/) do
  on(FlowPage).dialog_element.when_not_present
end

Then(/^the top post should be marked as deleted$/) do
  expect(on(FlowPage).flow_first_topic_moderation_msg_element.when_present.text).to match("This topic has been deleted")
end

Then(/^the top post should be marked as suppressed$/) do
  expect(on(FlowPage).flow_first_topic_moderation_msg_element.when_present.text).to match("This topic has been suppressed")
end
