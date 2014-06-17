Then(/^The header should say "(.*?)"$/) do |content|
  on(FlowPage) do |page|
    page.header_content_element.when_visible
    page.header_content.should match Regexp.escape( content )
  end
end

When(/^I click the edit header link$/) do
  on(FlowPage) do |page|
    page.header_content_element.when_present.hover
    page.edit_header_link_element.when_present.click
  end
end

Then(/^I see the edit header form$/) do
  on(FlowPage).edit_header_form_element.when_visible.should be_visible
end

When(/^I type "(.*?)" into the header textbox$/) do |arg1|
  on(FlowPage).edit_header_textbox_element.when_present.send_keys( arg1 )
end

When(/^I click Save$/) do
  on(FlowPage).edit_header_save_element.when_present.click
end
