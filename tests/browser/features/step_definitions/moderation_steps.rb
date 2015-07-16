When(/^I cancel the dialog$/) do
  on(FlowPage).dialog_cancel_element.when_present.click
end

When(/^I click Delete topic$/) do
  on(FlowPage).dialog_submit_delete_element.when_present.click
end

When(/^I click Hide topic$/) do
  on(FlowPage).dialog_submit_hide_element.when_present.click
end

When(/^I give reason for deletion as being "(.*?)"$/) do |delete_reason|
  step "I type \"#{delete_reason}\" in the dialog box"
end

When(/^I give reason for hiding as being "(.*?)"$/) do |hide_reason|
  step "I type \"#{hide_reason}\" in the dialog box"
end

When(/^I see a dialog box$/) do
  on(FlowPage).dialog_element.when_present
end

When(/^I type "(.*?)" in the dialog box$/) do |text|
  on(FlowPage).dialog_input_element.when_present.send_keys(text)
end

Then(/^I do not see the dialog box$/) do
  on(FlowPage).dialog_element.when_not_present
end

Then(/^the top post should be marked as deleted$/) do
  expect(on(FlowPage).flow_first_topic_moderation_msg_element.when_present.text).to match("This topic has been deleted")
end
