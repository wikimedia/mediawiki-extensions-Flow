Then(/^there should be a link to the main page in the first topic title$/) do
  on(NewTopicPage) do |page|
    page.flow_first_topic_main_page_link.when_present
  end
end

Then(/^there should be a red link in the first topic title$/) do
  on(NewTopicPage) do |page|
    page.flow_first_topic_red_link.when_present
  end
end

Then(/^there should be a media link in the first topic title$/) do
  on(NewTopicPage) do |page|
    page.flow_first_topic_media_link.when_present
  end
end
