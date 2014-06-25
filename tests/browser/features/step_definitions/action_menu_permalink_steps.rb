Then(/^I see only one topic on the page$/) do
  # We should have the a post with a heading
  on(FlowPage).flow_first_topic_heading_element.when_present
  # but this should match nothing - there is only one topic.
  on(FlowPage).div_element(css: '.flow-topic', index:1).when_not_present
end

When(/^I click Actions menu for the Topic$/) do
  on(FlowPage).topic_actions_link_element.when_present.click
end

When(/^I click Permalink from the Actions menu$/) do
  on(FlowPage).permalink_button_element.when_present.click
end

When(/^I click Permalink from the 3rd comment Post Actions menu$/) do
  on(FlowPage).actions_link_permalink_3rd_comment_element.when_present.click
end

When(/^I add (\d+) comments to the Topic$/) do |number|
  number.to_i.times do
    on(FlowPage) do |page|
      @saved_random=Random.new.rand.to_s
      step 'I reply with text "' + 'Selenium comment ' + @saved_random + '"'
    end
  end
end

When(/^I click the Post Actions link on the 3rd comment on the topic$/) do
  on(FlowPage).third_post_actions_link_element.when_present.click
end

When(/^I click Actions menu for the 3rd comment on the Topic$/) do
  on(FlowPage).actions_link_permalink_3rd_comment_element.when_present.click
end

Then(/^the highlighted comment should contain the text for the 3rd comment$/) do
  on(FlowPage).highlighted_post.should match @saved_random
end
