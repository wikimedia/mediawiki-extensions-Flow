
Given(/^I have suppressed and restored the first topic$/) do
  step 'I suppress the first topic'
  step 'I undo the suppression'
end

When(/^I click Suppress topic$/) do
  on(FlowPage).dialog_submit_suppress_element.when_present.click
end

When(/^I undo the suppression$/) do
  on(FlowPage) do |page|
    page.undo_suppression_button_element.when_present.click
    page.undo_suppression_button_element.when_not_visible
  end
end

When(/^I select to suppress the first topic$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_suppress_button_element
    page.select_menu_option menu, option
  end
end

When(/^I suppress the first topic with reason "(.*?)"$/) do |reason|
  step 'I select to suppress the first topic'
  step 'I see a dialog box'
  step "I type \"#{reason}\" in the dialog box"
  step 'I click Suppress topic'
  step 'the top post should be marked as suppressed'
end

When(/^I suppress the first topic$/) do
  step "I suppress the first topic with reason \"no reason given\""
end

Then(/^the top post should be marked as suppressed$/) do
  expect(on(FlowPage).flow_first_topic_moderation_msg_element.when_present.text).to match("This topic has been suppressed")
end
