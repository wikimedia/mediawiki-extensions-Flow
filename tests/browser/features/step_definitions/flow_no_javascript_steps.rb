Given(/^I am using user agent "(.+)"$/) do |user_agent|
  @user_agent = user_agent
  @browser = browser(test_name(@scenario), {user_agent: user_agent})
  $session_id = @browser.driver.instance_variable_get(:@bridge).session_id
end

Given(/^I am on a Flow page without JavaScript$/ ) do
  visit(FlowPage)
end

Then(/^I see the form to post a new topic$/) do
  on(FlowPage).new_topic_form_element.when_present.should be_visible
end

Then(/^the post new topic form has an add topic button$/) do
  on(FlowPage).new_topic_save_element.should be_visible
end

Then(/^the post new topic form has a preview button$/) do
  on(FlowPage).new_topic_preview_element.should be_visible
end

Then(/^the post new topic form does not have a cancel button$/) do
  on(FlowPage).new_topic_cancel_element.should_not be_visible
end

Then(/^I see the form to reply to a topic$/) do
  on(FlowPage).new_reply_form_element.should be_visible
end

Then(/^the post new reply form has an add topic button$/) do
  on(FlowPage).new_reply_save_element.should be_visible
end

Then(/^the post new reply form has a preview button$/) do
  on(FlowPage).new_reply_preview_element.should be_visible
end

Then(/^the post new reply form does not have a cancel button$/) do
  on(FlowPage).new_reply_cancel_element.should_not be_visible
end
