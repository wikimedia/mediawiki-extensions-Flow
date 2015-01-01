When(/^I navigate to the Recent Changes page$/) do
  visit(RecentChangesPage)
end

Then(/^the new topic should be in the Recent Changes page$/) do
  visit(RecentChangesPage)
  # FIXME T85654 RL check doesn't work with flow_no_javascript.feature, it assumes mw JS object.
  step "page has no ResourceLoader errors"
  expect(on(RecentChangesPage).recent_changes_element.when_present.text).to match @topic_string
end

Then(/^the new title should be in the Recent Changes page$/) do
  expect(on(RecentChangesPage).recent_changes_element.when_present.text).to match @edited_topic_string
end
