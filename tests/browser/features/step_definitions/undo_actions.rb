Given(/^I am on a Flow board "(.+)"$/) do |board_title|
  visit(NewFlowPage, using_params: { pagetitle: board_title })
end

Given(/^I am on a new Flow board with description "(.*?)"$/) do |content|
  @board_title = @data_manager.get 'Board_for_undo_actions'
  api.action('flow',
             submodule: 'edit-header',
             page: 'Flow_test_talk:New_page_' + @board_title,
             ehcontent: content,
             ehformat: 'wikitext')
  visit(NewFlowPage, using_params: { pagetitle: @board_title })
end

Given(/^I am on a new Flow board with topic content \"(.*?)\"$/) do |content|
  @board_title = @data_manager.get 'Board_for_undo_actions'
  topic_title = @data_manager.get 'Title_for_undo_actions'
  api.action('flow',
             submodule: 'new-topic',
             page: 'Flow_test_talk:New_page_' + @board_title,
             nttopic: topic_title,
             ntcontent: content)
  step "I am on a Flow board \"#{@board_title}\""
  on(NewFlowPage) do |page|
    page.refresh_until { page.topic_with_title(topic_title) }
  end
end

When(/^I visit the new board history page$/) do
  visit(BoardHistoryPage, using_params: { pagetitle: 'Flow_test_talk:New_page_' + @board_title })
  on(BoardHistoryPage).flow_board_history_element.when_present
end

Given(/^I edit the topic with \"(.*?)\"$/) do |content|
  step "I select Edit post"
  step "I edit the post field with \"#{content}\""
  step "I save the new post"
end

When(/^I click undo$/) do
  on(TopicHistoryPage) do |page|
    page.undo_link_element.when_present.click
  end
end

When(/^I am on a Flow page$/) do
  on(AbstractFlowPage) do |page|
    page.description.content_element.when_present
  end
end

When(/^I am on a Flow diff page$/) do
  on(FlowDiffPage) do |page|
    page.editor_element.when_present
  end
end

When(/^I undo the latest action$/) do
  step "I click undo"
  step "I am on a Flow diff page"
  step "I save the undo post"
end

When(/^I am on a Flow topic page$/) do
  on(AbstractFlowPage) do |page|
    page.flow_first_topic_element.when_present
  end
end

When(/^I save the undo post/) do
  on(FlowDiffPage) do |page|
    page.undo_post_save_element.when_present.click
    page.undo_post_save_element.when_not_present
  end
end

Then(/^the saved undo post should contain "(.+)"$/) do |undo_text|
  expect(on(FlowPage).flow_first_topic_body_element.when_present.text).to match(undo_text)
end
