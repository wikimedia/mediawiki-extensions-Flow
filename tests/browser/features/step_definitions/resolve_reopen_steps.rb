
When(/^I mark the first topic as resolved$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_resolve_button_element
    page.select_menu_option menu, option

    # dismiss the menu
    page.edit_summary_element.when_present.click
    page.edit_summary_element.when_present.focus
  end
end

When(/^I reopen the first topic$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_reopen_button_element
    page.select_menu_option menu, option

    # dismiss the menu
    page.edit_summary_element.when_present.click
    page.edit_summary_element.when_present.focus
  end
end

When(/^I skip the summary$/) do
  on(FlowPage) do |page|
    page.skip_summary_button_element.when_present.click
  end
end

Then(/^the first topic is resolved$/) do
  on(FlowPage) do |page|
    expect(page.first_topic_resolved_mark_element).to exist
  end
end

Then(/^the first topic is open$/) do
  on(FlowPage) do |page|
    expect(page.first_topic_resolved_mark_element).not_to exist
  end
end

When(/^I enter "(.*?)" as summary$/) do |summary_text|
  on(FlowPage) do |page|
    page.edit_summary_element.when_present.click
    page.edit_summary_element.when_present.focus
    page.edit_summary_element.when_present.clear
    page.edit_summary_element.when_present.send_keys summary_text
  end
end

When(/^I click the update summary button$/) do
  on(FlowPage) do |page|
    page.update_summary_button_element.when_present.click
    page.summary_content_element.when_present
  end
end

When(/^I click the summarize menu item$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_summarize_button_element
    page.select_menu_option menu, option
  end
end

When(/^I click the edit summary menu item$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.topic_edit_summary_button_element
    page.select_menu_option menu, option
  end
end

Then(/^the first topic is summarized as "(.*?)"$/) do |summary_text|
  on(FlowPage) do |page|
    expect(page.summary_content_element.when_present.text).to eq(summary_text)
  end
end

When(/^I keep the summary$/) do
  on(FlowPage) do |page|
    page.update_summary_button_element.when_present.click
  end
end

Then(/^the first topic is resolved with summary "(.*?)"$/) do |summary_text|
  step 'the first topic is resolved'
  step "the first topic is summarized as \"#{summary_text}\""
end

Given(/^I summarize the first topic as "(.*?)"$/) do |summary_text|
  step 'I click the summarize menu item'
  step "I enter \"#{summary_text}\" as summary"
  step 'I click the update summary button'
end

Given(/^I re-summarize the first topic as "(.*?)"$/) do |summary_text|
  step 'I click the edit summary menu item'
  step "I enter \"#{summary_text}\" as summary"
  step 'I click the update summary button'
end

When(/^I summarize as "(.*?)"$/) do |summary_text|
  step "I enter \"#{summary_text}\" as summary"
  step 'I click the update summary button'
end
