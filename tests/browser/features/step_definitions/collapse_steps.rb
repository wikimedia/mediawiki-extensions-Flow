When(/^I switch to Collapse view$/) do
  on(FlowPage).collapsed_view_element.when_present.click
end

When(/^I switch to Full view$/) do
  on(FlowPage).collapsed_view_element.when_present.click
  on(FlowPage).small_view_element.when_present.click
  on(FlowPage).full_view_element.when_present.click
end

When(/^I switch to Small view$/) do
  on(FlowPage).collapsed_view_element.when_present.click
  on(FlowPage).small_view_element.when_present.click
end

When(/^the page renders in (.+) seconds$/) do |seconds|
  sleep seconds.to_i
end

Then(/^I should see in Flow topics (.+)$/) do |topics_text|
  on(FlowPage).flow_topics.should match topics_text
end

Then(/^I should not see in Flow topics (.+)$/) do |topics_text|
  on(FlowPage).flow_topics.should_not match topics_text
end
