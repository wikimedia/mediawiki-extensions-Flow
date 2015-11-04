
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
    page.refresh_until { page.flow.board_element.visible? }
  end
end

Given(/^my talk page has wiktext content$/) do
  talk_page = "User_talk:#{@username}"
  @talk_page_content = "this is the content of my talk page"
  content = @talk_page_content
  content += "\n{{template_before_first_heading}}"
  content += "\n== this is the first section =="
  content += "\n{{template_after_first_heading}}"
  api.create_page talk_page, content
end

Then(/^my previous talk page is archived$/) do
  archive_name = "./User_talk:#{@username}/Archive_1"
  archive_template = 'This page is an archive.'
  visit(WikiPage, using_params: { page: archive_name }) do |page|
    expect(page.content_element.when_present.text).to match @talk_page_content
    expect(page.content_element.when_present.text).to match archive_template

    expect(page.content).to match 'This page is an archive.'
    expect(page.content).to_not match 'Previous discussion was archived at'
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
  flow_archive_link = "User_talk:#{@username}/Flow_Archive_1".tr '_', ' '
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    page.refresh_until do
      page.content.match @talk_page_content
    end
    expect(page.content).to_not match 'This page is an archive.'
    expect(page.content).to match 'Previous discussion was archived at'
    expect(page.content).to match flow_archive_link
  end
end

Then(/^my Flow board is archived$/) do
  flow_archive_name = "./User_talk:#{@username}/Flow_Archive_1"
  visit(WikiPage, using_params: { page: flow_archive_name }) do |page|
    page.refresh_until { page.flow.board_element.visible? }
    page.flow.board_element.when_present
    expect(page.flow.header).to match 'This page is an archive.'
    expect(page.flow.header).to_not match 'Previous discussion was archived at'
  end
end

Given(/^I have used the Flow beta feature before$/) do
  step 'my talk page has wiktext content'
  step 'I enable Flow beta feature'
  step 'my talk page is a Flow board'
  @topic_title = @data_manager.get 'title'
  api.action('flow', submodule: 'new-topic', page: "User_talk:#{@username}", nttopic: @topic_title, ntcontent: 'created via API')
  step 'I disable Flow beta feature'
  step 'my wikitext talk page is restored'
end

Then(/^my talk page is my old Flow board$/) do
  archive_name = "User_talk:#{@username}/Archive_1".tr '_', ' '
  visit(WikiPage, using_params: { page: "./User_talk:#{@username}" }) do |page|
    page.refresh_until { page.flow.board_element.visible? }
    page.flow.board_element.when_present

    expect(page.flow.header).to match archive_name
    expect(page.flow.header).to match 'Previous discussion was archived at'
    expect(page.flow.header).to_not match 'This page is an archive.'
  end
end

Then(/^my flow board contains a link to my archived talk page$/) do
  archive_name = "User_talk:#{@username}/Archive_1".tr '_', ' '
  visit(UserTalkPage, using_params: { username: @username }) do |page|
    page.refresh_until { page.flow.board_element.visible? }
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
