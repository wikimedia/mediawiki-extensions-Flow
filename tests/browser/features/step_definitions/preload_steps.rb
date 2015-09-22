Given(/^there is a page to preload content from$/) do
  page_name = 'Preloaded_body_example'
  @content_to_preload = 'this is the content of the preload page'
  api.create_page page_name, @content_to_preload
end

When(/^I am on Flow page with the title and content preload parameters$/) do
  @title = @data_manager.get 'new_topic_title'
  visit(PreloadedFlowPage,
        using_params: {
          topiclist_preloadtitle: @title,
          topiclist_preload: 'Preloaded_body_example'
        })
  step 'The Flow page is fully loaded'
  step 'page has no ResourceLoader errors'
end

Then(/^the title is preloaded$/) do
  on(AbstractFlowPage) do |page|
    expect(page.new_topic_title).to eq @title
  end
end

Then(/^the content is preloaded$/) do
  on(AbstractFlowPage) do |page|
    page.wait_until do
      page.new_topic_body_element.when_present.text
    end
    expect(page.new_topic_body_element.when_present.text).to eq @content_to_preload
  end
end

When(/^I am on Flow page with the title preload parameter$/) do
  @title = @data_manager.get 'new_topic_title'
  visit(PreloadedFlowPage, using_params: { topiclist_preloadtitle: @title })
  step 'The Flow page is fully loaded'
  step 'page has no ResourceLoader errors'
end

Then(/^the content is empty$/) do
  on(AbstractFlowPage) do |page|
    expect(page.new_topic_body_element.when_present.text).to eq ''
  end
end

When(/^I am on Flow page with the content preload parameter$/) do
  @title = @data_manager.get 'new_topic_title'
  visit(PreloadedFlowPage, using_params: { topiclist_preload: 'Preloaded_body_example' })
  step 'The Flow page is fully loaded'
  step 'page has no ResourceLoader errors'
end

Then(/^the title is empty$/) do
  on(AbstractFlowPage) do |page|
    expect(page.new_topic_title).to eq ''
  end
end
