Given(/^I click the Lock topic button$/) do
  on(FlowPage) do |page|
    page.topic_lock_button_element.when_present.focus
    page.topic_lock_button_element.click
  end
end

Given(/^I click the Unlock topic button$/) do
  on(FlowPage) do |page|
    page.topic_unlock_button_element.when_present.focus
    page.topic_unlock_button_element.click
  end
end

Given(/^the top post has been locked$/) do
  step 'I click the Topic Actions link'
  step 'I click the Lock topic button'
  step 'I type "This is a bikeshed" as the reason'
  step 'I submit the lock/unlock topic form'
end

When(/^I cancel the lock\/unlock topic form$/) do
  on(FlowPage).topic_lock_form_cancel_button_element.when_present.click
end

When(/^I expand the top post$/) do
  on(FlowPage).flow_first_topic_heading_element.when_present.click
end

When(/^I submit the lock\/unlock topic form$/) do
  on(FlowPage) do |page|
    page.topic_lock_form_lock_button_element.when_present.click
    page.topic_lock_form_lock_button_element.when_not_present
  end
end

When(/^I type "(.*?)" as the reason$/) do |reason|
  on(FlowPage) do |page|
    page.topic_lock_form_reason_element.when_present.clear
    # Focus textarea so that any menus that have been clicked lose their focus. In Chrome these might disrupt the test as
    # elements may be masked and not clickable.
    page.topic_lock_form_reason_element.click
    page.topic_lock_form_reason_element.send_keys(reason)
  end
end

Then(/^I should not see the lock\/unlock form$/) do
  on(FlowPage) do |page|
    page.topic_lock_form_element.when_not_present
    expect(page.topic_lock_form_element).not_to be_visible
  end
end

Then(/the original message for the top post should have no edit link$/) do
  expect(on(FlowPage).flow_first_topic_original_post_edit_element).not_to be_visible
end

Then(/^the original message for the top post should have no reply link$/) do
  expect(on(FlowPage).flow_first_topic_original_post_reply_element).not_to be_visible
end

Then(/^the reason of the first topic should be "(.*?)"$/) do |text|
  expect(on(FlowPage).flow_reason_element.text).to match text
end

Then(/^the top post should be a locked discussion$/) do
  expect(on(FlowPage).flow_first_topic_moderation_msg_element.when_present).to be_visible
end

Then(/^the top post should be an open discussion$/) do
  on(FlowPage) do |page|
    page.flow_first_topic_moderation_msg_element.when_not_present
    expect(page.flow_first_topic_moderation_msg_element).not_to be_visible
  end
end
