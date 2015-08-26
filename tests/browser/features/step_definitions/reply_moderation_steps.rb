When(/^I click Hide comment button$/) do
  on(FlowPage) do |page|
    page.actions_link_hide_3rd_comment_element.when_present.focus
    page.actions_link_hide_3rd_comment_element.click
  end
end

When(/^I click the Hide button in the dialog$/) do
  on(FlowPage) do |page|
    page.dialog_submit_hide_element.click
    page.dialog_submit_hide_element.when_not_present
  end
end

When(/^I hide the second comment with reason "(.*?)"$/) do |reason|
  on(FlowPage) do |page|
    menu = page.second_post_actions_link_element
    option = page.actions_link_hide_second_comment_element
    page.select_menu_option menu, option
  end
  step "I type \"#{reason}\" in the dialog box"
  step 'I click the Hide button in the dialog'
end

Then(/^the second comment should be marked as hidden$/) do
  on(FlowPage) do |page|
    page.second_post_element.when_present
    expect(page.second_post_moderation_msg).to match('This comment was hidden')
  end
end

Then(/^the content of the second comment should not be visible$/) do
  expect(on(FlowPage).second_post_content_element).not_to be_visible
end
