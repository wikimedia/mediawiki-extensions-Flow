
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

Then(/^my talk page is a flow board$/) do
  visit(UserTalkPage, using_params: {username: @username}) do |page|
    page.flow_board_element.when_present
  end
end

Given(/^my talk page has wiktext content$/) do
  talk_page = "User_talk:#{@username}"
  @talk_page_content = 'this is the content of my talk page'
  api.create_page talk_page, @talk_page_content
end

Then(/^my previous talk page is archived$/) do
  archive_name = "./User_talk:#{@username}/Archive_1"
  visit(WikiPage, using_params: {page: archive_name}) do |page|
    expect(page.content_element.when_present.text).to match @talk_page_content
  end
end
