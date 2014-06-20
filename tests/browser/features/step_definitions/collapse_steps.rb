# In order
COLLAPSE_STRING_TO_INDEX = {
  "Topics and posts" => 0,
  "Topics only" => 1,
  "Small topics" => 2
}

COLLAPSE_INDEX_TO_STRING = COLLAPSE_STRING_TO_INDEX.invert

When(/^I switch from (.*) view to (.*) view$/) do |start_mode, end_mode|
  on(FlowPage) do |page|
    # If current_index is the array index, what must be clicked to
    # get to the next one
    elementToClick = [
      page.topics_only_view_element,
      page.small_topics_view_element,
      page.topics_and_posts_view_element
    ];

    current_index = COLLAPSE_STRING_TO_INDEX[start_mode];
    current_mode = start_mode;
    while current_mode != end_mode do
      elementToClick[current_index].when_visible.click
      current_index = ( current_index + 1 ) % elementToClick.length
      current_mode = COLLAPSE_INDEX_TO_STRING[current_index]
    end
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

When(/^I click the first non-moderated topic$/) do
  on(FlowPage).first_non_moderated_topic_title_element.click
end

Then(/^I should (.*) the title of the first moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_moderated_topic_title_element, visibility_str)
end

Then(/^I should (.*) who started the first moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_moderated_topic_starter_element, visibility_str)
end

Then(/^I should (.*) the comments of the first moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_moderated_topic_post_content_element, visibility_str)
end

When(/^I click the first moderated topic$/) do
  on(FlowPage).first_moderated_topic_title_element.click
end

When(/^the page renders in (.+) seconds$/) do |seconds|
  sleep seconds.to_i
end
