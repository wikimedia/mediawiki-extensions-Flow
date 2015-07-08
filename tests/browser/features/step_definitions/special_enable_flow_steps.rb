When(/^I navigate to enable flow page$/) do
  visit(EnableFlowPage)
end

When(/^I enable a new Flow board$/) do
  on(EnableFlowPage) do |page|
    @new_board_page = @data_manager.get_talk 'Test_Random_Board'
    page.page_name_element.when_present.send_keys(@new_board_page)
    page.submit_button.when_present.click
  end
end

Then(/^I get confirmation for enabling a new Flow board$/) do
  expect(on(EnableFlowConfirmationPage).new_board_link.text).to match(@new_board_page)
end

Then(/^I click on the new Flow board link$/) do
  on(EnableFlowConfirmationPage).new_board_link.click
end

Then(/^The page I am on is a Flow board$/) do
  expect(on(NewRandomFlowPage).flow_board_element).to be_visible
end
