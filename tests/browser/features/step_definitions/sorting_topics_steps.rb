When(/^I click Newest topics choice$/) do
  on(FlowPage).newest_topics_choice_element.when_present.click
end

When(/^I click Newest topics link$/) do
  on(FlowPage).newest_topics_link_element.when_present.click
end

When(/^I click Recently active topics choice$/) do
  on(FlowPage).recently_active_topics_choice_element.when_present.click
end

When(/^I click Recently active topics link$/) do
  on(FlowPage) do |page|
    page.wait_until do
      !page.recently_active_topics_choice_element.visible?
    end
    page.recently_active_topics_link_element.when_present.click
  end
end

Then(/^the Flow page should not show Recently active topics link$/) do
  on(FlowPage) do |page|
    page.wait_until do
      !page.recently_active_topics_link_element.visible?
    end
    expect(on(FlowPage).recently_active_topics_link_element).not_to be_visible
  end
end

Then(/^the Flow page should show Recently active topics link$/) do
  expect(on(FlowPage).recently_active_topics_link_element.when_present).to be_visible
end

Then(/^the Flow page should not show Newest topics link$/) do
  on(FlowPage) do |page|
    page.wait_until do
      !page.newest_topics_link_element.visible?
    end
    expect(page.newest_topics_link_element).not_to be_visible
  end
end

Then(/^the Flow page should show Newest topics link$/) do
  expect(on(FlowPage).newest_topics_link_element.when_present).to be_visible
end

