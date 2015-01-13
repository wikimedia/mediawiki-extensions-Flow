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

Then(/^the 3rd comment should be marked as hidden$/) do
  on(FlowPage) do |page|
    page.third_reply_element.when_present
    expect(page.third_reply_moderation_msg).to match('This comment was hidden')
  end
end

Then(/^the content of the 3rd comment should not be visible$/) do
  expect(on(FlowPage).third_reply_content_element).not_to be_visible
end
