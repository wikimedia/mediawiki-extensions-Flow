Given(/^I am at Log in page$/) do
  visit LoginPage
end

When(/^I log in with incorrect password$/) do
  on(LoginPage).login_with(ENV['MEDIAWIKI_USER'], 'incorrect password')
end
When(/^I log in with incorrect username$/) do
  on(LoginPage).login_with('incorrect username', ENV['MEDIAWIKI_PASSWORD'])
end
When(/^I log in without entering credentials$/) do
  on(LoginPage).login_with('', '')
end
When(/^I log in without entering password$/) do
  on(LoginPage).login_with(ENV['MEDIAWIKI_USER'], '')
end
When(/^Log in as (.+)$/) do |username|
  on(LoginPage).login_with(username, ENV['MEDIAWIKI_PASSWORD'])
end

Then(/^feedback should be (.+)$/) do |feedback|
  on(LoginPage).feedback.should match Regexp.escape(feedback)
end
Then(/^Log in element should be there$/) do
  on(LoginPage).login_element.should exist
end
Then(/^Log in page should open$/) do
  @browser.url.should match Regexp.escape('Special:UserLogin')
end
Then(/^main page should open$/) do
  @browser.url.should == on(MainPage).class.url
end
Then(/^Password element should be there$/) do
  on(LoginPage).password_element.should exist
end
Then(/^there should be a link to (.+)$/) do |text|
  on(LoginPage).username_displayed_element.when_present.text.should == text
end
Then(/^Username element should be there$/) do
  on(LoginPage).username_element.should exist
end
