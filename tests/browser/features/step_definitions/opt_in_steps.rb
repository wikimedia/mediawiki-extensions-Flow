
Given(/^I am logged in as a new user$/) do
  @username = @data_manager.get 'New_user'
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
  visit(WikiPage, using_params: { page: archive_name }) do |page|
    expect(page.content_element.when_present.text).to match @talk_page_content
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
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    expect(page.content_element.when_present.text).to match @talk_page_content
  end
end

Then(/^my Flow board is archived$/) do
  flow_archive_name = "./User_talk:#{@username}/Flow_Archive_1"
  visit(WikiPage, using_params: { page: flow_archive_name }) do |page|
    page.flow.board_element.when_present
  end
end

Then(/^my talk page contains a link to my archived Flow board$/) do
  flow_archive_name = "User_talk:#{@username}/Flow_Archive_1"
  link_text = flow_archive_name.gsub '_', ' '
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    expect(page.content_element.when_present.text).to match link_text
  end
end

Given(/^I have used the Flow beta feature before$/) do
  step 'I enable Flow beta feature'
  @topic_title = @data_manager.get 'title'
  api.action('flow', submodule: 'new-topic', page: "User_talk:#{@username}", nttopic: @topic_title, ntcontent: 'created via API')
  step 'I disable Flow beta feature'
end

Then(/^my talk page is my old Flow board$/) do
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    expect(page.content_element.when_present.text).to match @topic_title
  end
end
