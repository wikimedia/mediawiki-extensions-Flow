Then(/^the description should be "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.description_content_element.when_present
    page.description_content.should eq(content)
  end
end

When(/^I set the description to "(.*?)"$/) do |description_text|
  on(FlowPage) do |page|
    page.edit_description_link_element.click
    page.edit_description_form_element.when_visible
    page.edit_description_textbox_element.when_present.clear
    page.edit_description_textbox_element.when_present.send_keys description_text
    page.edit_description_save_element.when_present.click
  end
end

Then(/^the description should be empty$/) do
  step "the description should be \"\""
end