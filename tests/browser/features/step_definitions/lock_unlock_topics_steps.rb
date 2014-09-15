Given(/^the page has re-rendered$/) do
  sleep 10
end

Given(/^the top post has been locked$/) do
  step 'I click the Topic Actions link'
  step 'I click the Lock topic button'
  step 'I type "This is a bikeshed" as the reason'
  step 'I submit the lock/unlock topic form'
  step 'the page has re-rendered'
end

Given(/^I click the Lock topic button$/) do
  on(FlowPage).topic_lock_button_element.when_present.click
end

Given(/^I click the Unlock topic button$/) do
  on(FlowPage).topic_unlock_button_element.when_present.click
end

When(/^I type "(.*?)" as the reason$/) do |reason|
  on(FlowPage).topic_lock_form_reason_element.when_present.clear()
  # Focus textarea so that any menus that have been clicked lose their focus. In Chrome these might disrupt the test as
  # elements may be masked and not clickable.
  on(FlowPage).topic_lock_form_reason_element.click
  on(FlowPage).topic_lock_form_reason_element.send_keys(reason)
end

When(/^I cancel the lock\/unlock topic form$/) do
  on(FlowPage).topic_lock_form_cancel_button_element.when_present.click
end

When(/^I submit the lock\/unlock topic form$/) do
  on(FlowPage).topic_lock_form_lock_button_element.when_present.click
end

Then(/^the top post is a locked discussion$/) do
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

Then(/^I do not see the lock\/unlock form$/) do
  on(FlowPage).topic_lock_form_element.when_not_present
end

Then(/^the original message for the top post has no reply link$/) do
  on(FlowPage).flow_first_topic_original_post_reply_element.should_not exist
end

Then(/the original message for the top post has no edit link$/) do
  on(FlowPage).flow_first_topic_original_post_edit_element.should_not exist
end

