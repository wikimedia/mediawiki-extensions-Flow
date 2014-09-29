When(/^I click Hide comment button$/) do
  on(FlowPage).actions_link_hide_3rd_comment_element.click
end

When(/^I click the Hide button in the dialog$/) do
  on(FlowPage) do |page|
    page..dialog_submit_element.click
    page.dialog_submit_element.when_not_present
  end
end

Then(/^the 3rd comment should be marked as hidden$/) do
  on(FlowPage) do |page|
    page.third_reply_element.when_present.should match /flow-post-moderated/
    page.third_reply_moderation_msg.should match( 'This comment was hidden' )
  end
end

Then (/^the content of the 3rd comment should not be visible$/) do
  on(FlowPage).third_reply_content_element.should_not be_visible
end
