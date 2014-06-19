Given(/^I am viewing the site in without JavaScript$/) do
  # Using IE5 user agent which is currently blocked by ResourceLoader
  user_agent = "Mozilla/4.0 (compatible; MSIE 5.5b1; Mac_PowerPC)"
  @user_agent = user_agent
  @browser = browser(test_name(@scenario), {user_agent: user_agent})
  $session_id = @browser.driver.instance_variable_get(:@bridge).session_id
end

Then(/^I see the form to post a new topic$/) do
  on(FlowPage).new_topic_form_element.should be_visible
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
