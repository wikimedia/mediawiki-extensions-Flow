# This is like 'I have created a Flow topic with title', but doesn't
# expect the input to be a one-to-one mapping to the output.  The
# problematic sub-step of that is "topic \"#{title}\" is saved"
Given(/^I have created a Flow topic containing the wikitext "(.+)"$/) do |title|
  step "I type \"#{title}\" into the new topic title field"
  step "I type \"#{title}\" into the new topic content field"
  step "I click New topic save"
end

Then(/^there should be a link to the main page in the first topic title$/) do
  on(FlowPage) do |page|
    page.flow_first_topic_main_page_link_element.when_present
  end
end

Then(/^there should be a red link in the first topic title$/) do
  on(FlowPage) do |page|
    page.flow_first_topic_red_link_element.when_present
  end
end
