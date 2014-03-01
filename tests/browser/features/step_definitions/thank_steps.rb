Then(/^I do not see a Thank button$/) do
  on(FlowPage).thank_button_element.should_not exist
end

Then(/^I do not see a Thanked button$/) do
  on(FlowPage).thanked_button_element.should_not exist
end

Then(/^I should see the Thank button be replaced with Thanked button$/) do
  # TODO: if we decide to use a loading indicator for the thank button
  # then condition can be changed to loading_indicator.when_not_present
  @target_container.span_element(class: 'mw-thanks-flow-thanked').when_visible
end

When(/^I see a Thank button$/) do
  on(FlowPage).thank_button_element.should be_visible
  @target_container = on(FlowPage).thank_button_element.parent
end

When(/^I click on the Thank button$/) do
  on(FlowPage).thank_button_element.click
end

Then(/^I should not see the Thank button for that post$/) do
  on(FlowPage).new_post_element.span_element(class: 'mw-thanks-flow-thanked').should_not be_visible
end
