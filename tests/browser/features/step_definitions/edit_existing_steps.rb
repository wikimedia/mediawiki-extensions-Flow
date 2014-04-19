Given( /has an editable post/ )do
  on(FlowPage) do |page|
    pending unless page.edit_post_element.exists?
  end
end

When(/^I click Edit post$/) do
  on(FlowPage) do |page|
    page.edit_post_element.when_present.click
  end
end

When(/^I click the Edit title action$/) do
  on(FlowPage) do |page|
    page.topic_actions_link_element.when_present.click
    page.edit_title_icon_element.when_present.click
  end
end

Then(/^I should be able to edit the post field with (.+)$/) do |edited_post|
  on(FlowPage).post_edit_element.when_present.send_keys(edited_post + @random_string)
end

Then(/^I should be able to edit the title field with (.+)$/) do |edited_title|
  on(FlowPage).title_edit_element.when_present.send_keys(edited_title + @random_string)
end

Then(/^I should be able to save the new post/) do
  on(FlowPage).change_post_save_element.when_present.click
end

Then(/^I should be able to save the new title$/) do
  on(FlowPage).change_title_save_element.when_present.click
end

Then(/^the saved post should contain (.+)$/) do |edited_post|
  on(FlowPage) do |page|
    page.small_spinner_element.when_not_present
    page.topic_post.should match(edited_post + @random_string)
  end
end

Then(/^the saved topic title should contain (.+)$/) do |edited_title|
  on(FlowPage) do |page|
    page.small_spinner_element.when_not_present
    page.topic_title.should match(edited_title + @random_string)
  end
end
