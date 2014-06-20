Given(/^the top post has been closed$/) do
  step 'I click the Topic Actions link'
  step 'I click the Close topic button'
  step 'I type "This is a bikeshed" as the reason'
  step 'I submit the close/reopen topic form'
  step 'the page renders in 2 seconds'
end

Given(/^I click the Close topic button$/) do
  on(FlowPage).topic_close_button_element.when_present.click
end

Given(/^I click the Reopen topic button$/) do
  on(FlowPage).topic_reopen_button_element.when_present.click
end

Given(/^I see a form to close the topic$/) do
  on(FlowPage).topic_close_form_element.when_present.should be_visible
end

When(/^I type "(.*?)" as the reason$/) do |reason|
  on(FlowPage).topic_close_form_reason_element.when_present.clear()
  on(FlowPage).topic_close_form_reason_element.send_keys(reason)
end

When(/^I cancel the close\/reopen topic form$/) do
  on(FlowPage).topic_close_form_cancel_button_element.when_present.click
end

When(/^I submit the close\/reopen topic form$/) do
  on(FlowPage).topic_close_form_close_button_element.when_present.click
end

Then(/^the top post is a closed discussion$/) do
  on(FlowPage).flow_first_topic_moderation_msg_element.when_present.should be_visible
end

Then(/^the top post is an open discussion$/) do
  on(FlowPage).flow_first_topic_moderation_msg_element.when_not_present
end

Then(/^the topic summary of the first topic is "(.*?)"$/) do |text|
  on(FlowPage).flow_first_topic_summary_element.text.should match text
end

Then(/^I expand the top post$/) do
  on(FlowPage).flow_first_topic_heading_element.when_present.click
end

Then(/^I do not see the close\/reopen form$/) do
  on(FlowPage).topic_close_form_element.when_not_present
end

