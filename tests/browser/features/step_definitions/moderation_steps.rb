# TODO (mattflaschen, 2014-06-25): Have the below actions (e.g. 'I
# click Delete topic') wait for the dialog box to be non-visible
# afterwards (to confirm API call finished), rather than use
# timeouts?
When(/^I cancel the dialog$/) do
  on(FlowPage).dialog_cancel_element.when_present.click
end

When(/^I click the dialog's Delete button$/) do
  on(FlowPage).dialog_delete_button_element.when_present.click
end

When(/^I click the dialog's Hide button$/) do
  on(FlowPage).dialog_hide_button_element.when_present.click
end

When(/^I click the dialog's Suppress button$/) do
  on(FlowPage).dialog_suppress_button_element.when_present.click
end

When(/^I give as reason for moderation "(.*?)"$/) do |moderation_reason|
  on(FlowPage) do |page|
    page.dialog_input_element.when_present.send_keys(moderation_reason)
  end
end

When(/^I see a dialog box$/) do
  on(FlowPage).dialog_element.when_present.should be_visible
end

Then(/^the dialog's Delete button should be disabled$/) do
  val = (FlowPage).dialog_delete_button_element.attribute( "disabled" )
  expect(val).to eq("true")
end

Then(/^I confirm$/) do
  on(FlowPage).confirm(true){}
end

Then(/^I do not see the dialog box$/) do
  on(FlowPage).dialog_element.when_not_present
end

Then(/^the first topic should be moderated as (.+)$/) do |moderation_type|
  expect(on(FlowPage).flow_first_topic_moderation_msg.when_present).to match('This topic has been ' + moderation_type)
end
