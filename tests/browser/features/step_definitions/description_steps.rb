Then(/^the description should be "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.description.content.should eq(content)
  end
end

When(/^I set the description to "(.*?)"$/) do |description_text|
  on(FlowPage) do |page|
    page.description.edit
    page.description.editor_element.when_present.clear
    page.description.editor_element.when_present.send_keys description_text
    page.description.save
    page.description.content_element.when_present
  end
end

Then(/^the description should be empty$/) do
  step "the description should be \"\""
end
