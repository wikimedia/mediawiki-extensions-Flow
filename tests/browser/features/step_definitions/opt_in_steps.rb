
Given(/^I am logged in as a new user$/) do
  @username = @data_manager.get 'New_user'
  puts "New user: #{@username}"
  api.create_account @username, password
  visit(LoginPage).login_with @username, password
end

When(/^I enable Flow beta feature$/) do
  visit(SpecialPreferencesPage) do |page|
    page.beta_features_element.when_present.click
    page.check_flow_beta_feature
    page.save_preferences
    page.confirmation_element.when_present
  end
end

Then(/^my talk page is a Flow board$/) do
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    page.flow.board_element.when_present
  end
end

Given(/^my talk page has wiktext content$/) do
  talk_page = "User_talk:#{@username}"
  @talk_page_content = 'this is the content of my talk page'
  api.create_page talk_page, @talk_page_content
end

Then(/^my previous talk page is archived$/) do
  archive_name = "./User_talk:#{@username}/Archive_1"
  archive_template = 'This page is an archive.'
  visit(WikiPage, using_params: { page: archive_name }) do |page|
    expect(page.content_element.when_present.text).to match @talk_page_content
    expect(page.content_element.when_present.text).to match archive_template
  end
end

Given(/^I have Flow beta feature enabled$/) do
  step 'I enable Flow beta feature'
end

When(/^I disable Flow beta feature$/) do
  visit(SpecialPreferencesPage) do |page|
    page.beta_features_element.when_present.click
    page.uncheck_flow_beta_feature
    page.save_preferences
    page.confirmation_element.when_present
  end
end

Then(/^my wikitext talk page is restored$/) do
  talk_page_link = "User_talk:#{@username}".gsub '_', ' '
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    page.content_element.when_present
    expect(page.content).to match @talk_page_content
    expect(page.content).to_not match talk_page_link
  end
end

Then(/^my Flow board is archived$/) do
  flow_archive_name = "./User_talk:#{@username}/Flow_Archive_1"
  talk_page_link = "User_talk:#{@username}".gsub '_', ' '
  visit(WikiPage, using_params: { page: flow_archive_name }) do |page|
    page.flow.board_element.when_present
    expect(page.flow.header).to_not match talk_page_link
  end
end

Given(/^I have used the Flow beta feature before$/) do
  step 'my talk page has wiktext content'
  step 'I enable Flow beta feature'
  @topic_title = @data_manager.get 'title'
  api.action('flow', submodule: 'new-topic', page: "User_talk:#{@username}", nttopic: @topic_title, ntcontent: 'created via API')
  step 'I disable Flow beta feature'
end

Then(/^my talk page is my old Flow board$/) do
  archive_name = "User_talk:#{@username}/Archive_1".gsub '_', ' '
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    expect(page.content_element.when_present.text).to match @topic_title
    expect(page.flow.header).to match archive_name
  end
end

Then(/^my flow board contains a link to my archived talk page$/) do
  archive_name = "User_talk:#{@username}/Archive_1".gsub '_', ' '
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    page.flow.board_element.when_present
    expect(page.flow.header).to match archive_name
  end
end

Then(/^a notification tells me about it$/) do
  visit(SpecialNotificationsPage) do |page|
    expect(page.first_notification_element.when_present.text).to match 'New discussion system'
  end
end

Then(/^my talk page is deleted without redirect$/) do
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    page.content_element.when_present
    expect(page.content).to match 'This page has been deleted.'
    expect(page.content).to match 'without leaving a redirect'
  end
end
