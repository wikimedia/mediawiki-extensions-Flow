# This test has no javascript
# Therefore this test has no AJAX
# Therefore it should run without any "when_present" clauses
# If you need a "when_present" to make the test run, that is a bug

Given(/^I am using user agent "(.+)"$/) do |user_agent|
  @user_agent = user_agent
  @browser = browser(test_name(@scenario), {user_agent: user_agent})
  $session_id = @browser.driver.instance_variable_get(:@bridge).session_id
end

Given(/^I am on a Flow page without JavaScript$/ ) do
  visit(FlowPage)
end

When(/^I click Add topic no javascript$/) do
  on(FlowPage).add_topic_no_javascript_element.click
end

When(/^I enter a no javascript topic title of "(.*?)"$/) do |no_javascript_topic_title|
  @no_javascript_topic_title = no_javascript_topic_title
  on(FlowPage).no_javascript_topic_title_text_element.send_keys "#{@no_javascript_topic_title} #{@random_string}"
end

When(/^I enter a no javascript topic body of "(.*?)"$/) do |no_javascript_topic_body|
  @no_javascript_topic_body = no_javascript_topic_body
  on(FlowPage).no_javascript_topic_body_text_element.send_keys "#{@no_javascript_topic_body} #{@random_string}"
end

When(/^I save a no javascript new topic$/) do
  on(FlowPage).add_topic_no_javascript_element.click
end

Then(/^the page contains my no javascript topic$/) do
  expect(on(FlowPage).no_javascript_page_content_title).to match "#{@no_javascript_topic_title} #{@random_string}"
end

Then(/^the page contains my no javascript body$/) do
  expect(on(FlowPage).no_javascript_page_content_body).to match "#{@no_javascript_topic_body} #{@random_string}"
end

Then(/^I see the form to post a new topic$/) do
  on(FlowPage) do |page|
    page.start_topic_no_js_element.click
    # the following step is so we don't have to define a different page object after clicking the link, just wait for the URL to change
    #page.wait_until  { @browser.url =~ /action=new-topic/ }
    page.new_topic_form_element.should be_visible
  end
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
