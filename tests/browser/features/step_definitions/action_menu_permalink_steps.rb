When(/^I click Actions menu for the Topic$/) do
  on(FlowPage).topic_actions_link_element.when_present.click
end

When(/^I click Permalink from the Actions menu$/) do
  on(FlowPage).permalink_element.when_present.click
end

When(/^I add (\d+) comments to the Topic$/) do |number|
  number.to_i.times do
  	on(FlowPage) do |page|
      @saved_random=Random.new.rand.to_s
      page.small_spinner_element.when_not_present
      page.comment_field_element.when_present.click
      page.comment_field_element.send_keys("Selenium comment " + @saved_random)
      page.comment_reply_save_element.when_present.click
    end
  end
end

When(/^I click Actions menu for the 3rd comment on the Topic$/) do
  on(FlowPage).actions_link_permalink_3rd_comment_element.when_present.click
end

Then(/^the highlighted comment should contain the text for the 3rd comment$/) do
  on(FlowPage).highlighted_comment.should match @saved_random
end
