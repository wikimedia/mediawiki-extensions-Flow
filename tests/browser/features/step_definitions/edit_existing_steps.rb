When(/^I click Edit post$/) do
  on(FlowPage) do |page|
    page.edit_post_element.when_present.click
  end
end

When(/^I click the Edit title action$/) do
  on(FlowPage) do |page|
    page.topic_actions_link_element.when_present.click
    page.edit_title_button_element.when_present.click
  end
end

When(/^I edit the post field with (.+)$/) do |edited_post|
  on(FlowPage) do |page|
    # Take focus away from menu
    page.post_edit_element.when_present.click
    page.post_edit_element.when_present.send_keys(edited_post + @random_string)
  end
end

When(/^I edit the title field with (.+)$/) do |edited_title|
  on(FlowPage) do |page|
    # Take focus away from menu
    page.title_edit_element.when_present.click
    page.title_edit_element.when_present.send_keys(edited_title + @random_string)
  end
end

When(/^I save the new post/) do
  on(FlowPage) do |page|
    page.change_post_save_element.when_present.click
    page.change_post_save_element.when_not_present
  end
end

When(/^I save the new title$/) do
  on(FlowPage).change_title_save_element.when_present.click
end

Then(/^the saved post should contain (.+)$/) do |edited_post|
  expect(on(FlowPage).flow_first_topic_body).to match(edited_post + @random_string)
end
