# This test has no javascript
# Therefore this test has no AJAX
# Therefore it should run without any "when_present" clauses
# If you need a "when_present" to make the test run, that is a bug

Given(/^I am on a Flow page without JavaScript$/) do
  visit(FlowPage)
end

Given(/^I am using a nojs browser$/) do
  # The following user-agent string contains:
  #   SymbianOS: for RL to NOT load the modern experience
  #   SMART-TV-SamsungBrowser: to bypass mobile-frontend and stay on the desktop site
  browser_factory.override(browser_user_agent: 'SymbianOS,SMART-TV-SamsungBrowser')
end

When(/^I click Add topic no javascript$/) do
  on(FlowPage).no_javascript_topic_title_text_element.click
end

When(/^I enter a no javascript reply of "(.*?)"$/) do |no_javascript_reply|
  @no_javascript_reply = no_javascript_reply
  on(FlowPage).no_javascript_reply_form_element.send_keys "#{@no_javascript_reply} #{@random_string}"
end

When(/^I enter a no javascript topic body of "(.*?)"$/) do |no_javascript_topic_body|
  @no_javascript_topic_body = no_javascript_topic_body
  on(FlowPage).no_javascript_topic_body_text_element.send_keys "#{@no_javascript_topic_body} #{@random_string}"
end

When(/^I enter a no javascript topic title of "(.*?)"$/) do |no_javascript_topic_title|
  @no_javascript_topic_title = no_javascript_topic_title
  on(FlowPage).no_javascript_topic_title_text_element.send_keys "#{@no_javascript_topic_title} #{@random_string}"
end

When(/^I save a no javascript new topic$/) do
  on(FlowPage).no_javascript_add_topic_element.click
end

When(/^I save a no javascript reply$/) do
  on(FlowPage).no_javascript_reply_element.click
end

When(/^I see the form to post a new topic$/) do
  on(FlowPage) do |page|
    page.no_javascript_start_topic_element.click
  end
end

When(/^I see the form to reply to a topic$/) do
  on(FlowPage) do |page|
    page.no_javascript_start_reply_element.click
  end
end

Then(/^the page contains my no javascript body$/) do
  expect(on(FlowPage).no_javascript_page_content_body).to match "#{@no_javascript_topic_body} #{@random_string}"
end

Then(/^the page contains my no javascript topic$/) do
  expect(on(FlowPage).no_javascript_page_content_title).to match "#{@no_javascript_topic_title} #{@random_string}"
end

Then(/^the page contains my no javascript reply$/) do
  expect(on(FlowPage).no_javascript_page_flow_topics).to match "#{@no_javascript_reply} #{@random_string}"
end
