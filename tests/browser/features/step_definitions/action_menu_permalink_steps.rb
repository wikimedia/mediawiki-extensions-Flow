When(/^I add (\d+) comments to the Topic$/) do |number|
  number.to_i.times do
    @saved_random = Random.new.rand.to_s
    step 'I reply with comment "' + 'Selenium comment ' + @saved_random + '"'
  end
end

When(/^I select the permalink for the first post of the first topic$/) do
  on(FlowPage) do |page|
    menu = page.post_actions_link_element
    option = page.permalink_button_element
    page.select_menu_option menu, option
  end
end

When(/^I select the permalink for the second post of the first topic$/) do
  on(FlowPage) do |page|
    menu = page.second_post_actions_link_element
    option = page.actions_link_permalink_second_comment_element
    page.select_menu_option menu, option
  end
end

When(/^I click Permalink from the Actions menu$/) do
  on(FlowPage).permalink_button_element.when_present.click
end

When(/^I click Permalink from the 3rd comment Post Actions menu$/) do
  on(FlowPage).actions_link_permalink_3rd_comment_element.when_present.click
end

When(/^I go to an old style permalink to my topic$/) do
  on(FlowPage) do |page|
    work_flow_id = page.flow_first_topic_element.attribute('data-flow-id')
    visit(FlowOldPermalinkPage, using_params: { workflow_id: work_flow_id })
  end
end

Then(/^I see only one topic on the page$/) do
  on(FlowPage) do |page|
    # We should have the a post with a heading
    expect(page.flow_first_topic_heading_element.when_present).to be_visible
    # but this should match nothing - there is only one topic.
    expect(page.flow_second_topic_heading_element).not_to be_visible
  end
end

Then(/^the highlighted comment is "(.*?)"$/) do |post_text|
  expect(on(FlowPage).highlighted_post).to match post_text
end

Then(/^the highlighted comment should contain the text for the 3rd comment$/) do
  expect(on(FlowPage).highlighted_post).to match @saved_random
end
