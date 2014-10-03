def visibility_to_should(el, visibility_str)
  if visibility_str == 'see'
    expect(el).to be_visible
  elsif visibility_str == 'not see'
    expect(el).not_to be_visible
  end
end

# TODO belongs in moderation_steps
When(/^I click the first moderated topic$/) do
  on(FlowPage).first_moderated_topic_titlebar_element.click
end

When(/^I click the first topic$/) do
  # Careful, the title might some day do something special.
  on(FlowPage).flow_first_topic_heading_element.click
end

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
    element_to_click = [
      page.topics_only_view_element,
      page.small_topics_view_element,
      page.topics_and_posts_view_element
    ]

    current_index = COLLAPSE_STRING_TO_INDEX[start_mode]
    current_mode = start_mode
    while current_mode != end_mode do
      element_to_click[current_index].when_visible.click
      current_index = (current_index + 1) % element_to_click.length
      current_mode = COLLAPSE_INDEX_TO_STRING[current_index]
    end
  end
end

Then(/^I should (.*) a moderated message on the first moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_moderated_message_element, visibility_str)
end

Then(/^I should (.*) the comments of the first moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_moderated_topic_post_content_element, visibility_str)
end

Then(/^I should (.*) the comments of the first topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).flow_first_topic_body_element, visibility_str)
end

Then(/^I should (.*) the title of the first moderated topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).first_moderated_topic_title_element, visibility_str)
end

Then(/^I should (.*) the title of the first topic$/) do |visibility_str|
  visibility_to_should(on(FlowPage).flow_first_topic_heading_element, visibility_str)
end
