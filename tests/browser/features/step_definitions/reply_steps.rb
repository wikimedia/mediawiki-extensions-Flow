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


Given(/^I click the reply input box$/) do
  on(FlowPage) do |page|
    page.new_reply_input_element.when_present.click
  end
end

Given(/^the reply button is disabled$/) do
  on(FlowPage).new_reply_save_element.disabled?
end
