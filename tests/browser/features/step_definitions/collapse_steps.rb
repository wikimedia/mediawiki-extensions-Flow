Given(/^I create a regular topic$/) do
  on(FlowPage) do |page|
    step "I create a Regular Topic in Flow new topic"
    step "I click New topic save"
  end
end

Given(/^I create a hidden topic$/) do
  on(FlowPage) do |page|
    step "I create a Hidden Topic in Flow new topic"
    step "I click New topic save"
    step "I click the Topic Actions link"
    page.topic_hide_button_element.when_visible.click
    page.topic_reason_element.when_present.send_keys("To test moderation")
    page.topic_submit
  end
end

# All of these assume it's starting from topics and posts view
When(/^I switch to Topics only view$/) do
  on(FlowPage).topics_only_view_element.when_visible.click
end

When(/^I switch to Small topics view$/) do
  on(FlowPage).topics_only_view_element.when_visible.click
  on(FlowPage).small_view_element.when_visible.click
end

# This starts from topics and posts view and implicitly tests that
# three clicks cycles back to the beginning
When(/^I switch to Topics and posts view$/) do
  on(FlowPage).topics_only_view_element.when_visible.click
  on(FlowPage).small_view_element.when_visible.click
  on(FlowPage).topics_and_posts_view_element.when_visible.click
end

When(/^the page renders in (.+) seconds$/) do |seconds|
  sleep seconds.to_i
end
