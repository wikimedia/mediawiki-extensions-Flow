Given(/^I navigate to enable flow page$/) do
  visit(EnableFlowPage)
end

Given(/^I have an existing talk page$/) do
  @new_board_page = @data_manager.get_talk 'Test_Prefilled_Random_Board'
  api.create_page @new_board_page, '<p class="flow-test-archive-content">Some wikitext here.</p>'
end

When(/^I enable a new Flow board on the talk page$/) do
  step "I enable a new Flow board on article #{@new_board_page}"
end

When(/^I enable a new Flow board$/) do
  @new_board_page = @data_manager.get_talk 'Test_Random_Board'
  step "I enable a new Flow board on article #{@new_board_page}"
end

When(/^I enable a new Flow board on article (.*?)$/) do |article|
  on(EnableFlowPage) do |page|
    page.page_name.when_present.send_keys(article)
    page.submit_button.when_present.click
  end
end

Then(/^I get confirmation for enabling a new Flow board$/) do
  expect(on(EnableFlowConfirmationPage).new_board_link.text).to match(@new_board_page)
end

Then(/^I click on the new Flow board link$/) do
  on(EnableFlowConfirmationPage).new_board_link.when_present.click
end

Then(/^The page I am on is a Flow board$/) do
  expect(on(AbstractFlowPage).flow_board_element).to be_visible
end

Then(/^I click the archive link$/) do
  on(AbstractFlowPage) do |page|
    page.sidebar_toggle_element.when_present.click unless page.description_content_element.visible?
    page.description_archive_link.when_present.click
  end
end

Then(/^The archive contains the original text$/) do
  expect(on(SpecialConversionFlowArchivePage).content_element.when_present.text).to match('Some wikitext here.')
end
