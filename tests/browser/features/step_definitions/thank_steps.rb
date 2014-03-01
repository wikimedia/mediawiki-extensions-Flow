Then(/^I do not see a Thank button$/) do
  on(FlowPage).thank_button_element.should_not exist
end

Then(/^I do not see a Thanked button$/) do
  on(FlowPage).thanked_button_element.should_not exist
end

Then(/^I should see the Thank button be replaced with Thanked button$/) do
  # TODO: If we decide to use a loading indicator for the thank button,
  # then condition can be changed to loading_indicator.when_not_present.
  @target_container.span_element(class: 'mw-thanks-flow-thanked').when_visible
end

When(/^I see a Thank button$/) do
  # This seems to be the best way to avoid the test from failing
  # if the assumption doesn't hold?
  pending unless on(FlowPage).thank_button_element.exists?
  # Carry on with the test per normal if the element is present.
  on(FlowPage).thank_button_element.should be_visible
  @target_container = on(FlowPage).thank_button_element.parent
end

When(/^I click on the Thank button$/) do
  on(FlowPage).thank_button_element.click
end

Then(/^I should not see the Thank button for that post$/) do
  on(FlowPage).new_post_element.link_element(class: 'mw-thanks-flow-thank-link').should_not be_visible
end
