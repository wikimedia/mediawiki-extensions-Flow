When(/^I select Edit post$/) do
  on(FlowPage) do |page|
    menu = page.post_actions_link_element
    option = page.edit_post_button_element
    page.select_menu_option menu, option
  end
end

When(/^I select the Edit title action$/) do
  on(FlowPage) do |page|
    menu = page.topic_actions_link_element
    option = page.edit_title_button_element
    page.select_menu_option menu, option
  end
end

When(/^I edit the post field with "(.+)"$/) do |edited_post|
  on(FlowPage) do |page|
    # Take focus away from menu
    page.post_edit_element.when_present.click
    page.post_edit_element.when_present.send_keys(edited_post + @random_string)
  end
end

When(/^I edit the title field with "(.+)"$/) do |edited_title|
  on(FlowPage) do |page|
    @edited_topic_string = edited_title + @random_string
    # Take focus away from menu
    page.title_edit_element.when_present.when_enabled.click
    page.title_edit = @edited_topic_string
  end
end

When(/^I save the new post/) do
  on(FlowPage) do |page|
    page.change_post_save_element.when_present.click
    page.change_post_save_element.when_not_present
  end
end

When(/^I save the new title$/) do
  on(FlowPage) do |page|
    page.change_title_save_element.when_present.click
    page.flow_first_topic_heading_element.when_present
  end
end

Then(/^the saved post should contain "(.+)"$/) do |edited_post|
  expect(on(FlowPage).flow_first_topic_body_element.when_present.text).to match(edited_post + @random_string)
end
