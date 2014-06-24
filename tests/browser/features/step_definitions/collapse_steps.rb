Given(/^I create a non-moderated topic$/) do
  step "I create a Non-Moderated Topic in Flow new topic"
  step "I create a Initial post of Non-Moderated Topic into Flow body"
  step "I click New topic save"
end

# All of these assume it's starting from topics and posts view
When(/^I switch to Topics only view$/) do
  on(FlowPage).topics_only_view_element.when_visible.click
end

When(/^I switch to Small topics view$/) do
  on(FlowPage) do |page|
    page.topics_only_view_element.when_visible.click
    page.small_topics_view_element.when_visible.click
  end
end

# This starts from topics and posts view and implicitly tests that
# three clicks cycles back to the beginning
When(/^I switch to Topics and posts view$/) do
  on(FlowPage) do |page|
    page.topics_only_view_element.when_visible.click
    page.small_topics_view_element.when_visible.click
    page.topics_and_posts_view_element.when_visible.click
  end
end

def visibility_to_should(el, visibility_str)
  if visibility_str === 'see'
    el.should be_visible
  elsif visibility_str === 'not see'
    el.should_not be_visible
  end
end

Then(/^I should (.*) the title of the first non-moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_non_moderated_topic_title_element, visibility_str)
end

Then(/^I should (.*) who started the first non-moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_non_moderated_topic_starter_element, visibility_str)
end

Then(/^I should (.*) the comments of the first non-moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_non_moderated_topic_post_content_element, visibility_str)
end


When(/^the page renders in (.+) seconds$/) do |seconds|
  sleep seconds.to_i
end
