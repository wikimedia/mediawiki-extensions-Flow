Then(/^I reply with comment "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.new_reply_save_element.when_not_present
    page.new_reply_input_element.when_present.click
    page.new_reply_input_element.send_keys(content)
    page.new_reply_save_element.when_present.click
    page.new_reply_save_element.when_not_present
  end
end

Then(/^the top post's first reply contains the text "(.+)"$/) do |text|
  on(FlowPage) do |page|
    page.new_reply_save_element.when_not_present
    page.first_reply_body.should match(text)
  end
end

Given(/^I am not watching my new Flow topic$/) do
  on(FlowPage) do |page|
    page.first_topic_unwatch_link_element.should be_visible
    page.first_topic_unwatch_link_element.click
    page.wait_until { page.first_topic_unwatch_link_element.visible? === false }
    page.wait_until {page.first_topic_watchlist_loading_link_element.visible? === false}
  end
end

Then(/^I should see an unwatch link on the topic$/) do
  on(FlowPage) do |page|
    page.first_topic_unwatch_link_element.should be_visible
  end
end

Then(/^I start a reply with comment "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.new_reply_save_element.when_not_present
    page.new_reply_input_element.when_present.click
    page.new_reply_input_element.send_keys(content)
  end
end

Then(/^I click the Preview button$/) do
  on(FlowPage) do |page|
    page.new_reply_preview_element.when_present.click
	page.wait_until { page.new_reply_preview_warning_element.visible? }
#    sleep(1)
  end
end

Then(/^I click the Cancel button and confirm the dialog$/) do
  on(FlowPage) do |page|
    page.confirm(true) do
      page.new_reply_cancel_element.when_present.click
	end
  end
end

Then(/^I should see the topic reply form$/) do
  on(FlowPage) do |page|
    page.wait_until { page.new_reply_input_element.visible? }
  end
end

