# TODO (mattflaschen, 2014-06-25): Have the below actions (e.g. 'I
# click Delete topic') wait for the dialog box to be non-visible
# afterwards (to confirm API call finished), rather than use
# timeouts?
When(/^I see a dialog box$/) do
  on(FlowPage).dialog_element.when_present.should be_visible
end

When(/^I give reason for hiding as being "(.*?)"$/) do |hide_reason|
  on(FlowPage) do |page|
    page.dialog_input_element.when_present.send_keys(hide_reason)
  end
end

When(/^I click Hide topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

When(/^I give reason for deletion as being "(.*?)"$/) do |delete_reason|
  on(FlowPage) do |page|
    page.dialog_input_expand_element.when_present.click
    page.dialog_input_element.when_present.send_keys(delete_reason)
  end
end

When(/^I click Delete topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

Then(/^the top post should be marked as deleted$/) do
  on(FlowPage).flow_first_topic_moderation_msg.when_present.should match( 'This topic was deleted' )
end

When(/^I give reason for suppression as being "(.*?)"$/) do |suppress_reason|
  on(FlowPage) do |page|
    page.dialog_input_expand_element.when_present.click
    page.dialog_input_element.when_present.send_keys(suppress_reason)
  end
end

When(/^I click Suppress topic$/) do
  on(FlowPage).dialog_submit_element.when_present.click
end

Then(/^the top post should be marked as suppressed$/) do
  on(FlowPage).flow_first_topic_moderation_msg.when_present.should match( 'This topic was suppressed' )
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
