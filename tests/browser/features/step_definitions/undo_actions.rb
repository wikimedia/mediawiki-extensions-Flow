When(/^I click undo$/) do
  on(TopicHistoryPage) do |page|
    page.undo_link_element.when_present.click
  end
end

When(/^I am on a Flow diff page$/) do
  on(FlowDiffPage) do |page|
    page.editor_element.when_present.visible?
  end
end

When(/^I am on a Flow topic page$/) do
  on(AbstractFlowPage) do |page|
    page.flow_first_topic_element.when_present.visible?
  end
end

When(/^I save the undo post/) do
  on(FlowDiffPage) do |page|
    page.undo_post_save_element.when_present.click
  end
end

Then(/^the saved undo post should contain "(.+)"$/) do |undo_text|
  expect(on(FlowPage).flow_first_topic_body_element.when_present.text).to match(undo_text)
end
