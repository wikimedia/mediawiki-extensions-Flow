Given(/^I am not watching my new Flow topic$/) do
  on(FlowPage) do |page|
    page.first_topic_unwatch_link_element.when_present.click
    page.first_topic_watch_link_element.when_present
  end
end

When(/^I reply with comment "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.new_reply_save_element.when_not_present
    page.first_reply_placeholder_element.when_present.click
    page.new_reply_editor_element.when_present.send_keys(content)
    page.new_reply_save_element.when_present.click
    page.new_reply_save_element.when_not_present
    page.flow_first_topic_element.paragraph_element(text: content).when_present
  end
end

When(/^I start a reply with comment "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.new_reply_save_element.when_not_present
    page.new_reply_placeholder_element.when_present.click
    page.new_reply_editor_element.send_keys(content)
  end
end

Then(/^I should see an unwatch link on the topic$/) do
  expect(on(FlowPage).first_topic_unwatch_link_element).to be_visible
end

Then(/^the top post's first reply should contain the text "(.+)"$/) do |text|
  on(FlowPage) do |page|
    page.new_reply_save_element.when_not_present
    expect(page.first_reply_body).to match(text)
  end
end

Then(/^I should see the topic reply form$/) do
  on(FlowPage) do |page|
    page.wait_until { page.new_reply_editor_element.visible? }
    expect(page.new_reply_editor_element).to be_visible
  end
end
