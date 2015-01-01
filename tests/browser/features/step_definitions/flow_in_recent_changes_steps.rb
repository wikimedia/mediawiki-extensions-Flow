When(/^I navigate to the Recent Changes page$/) do
  # It does not reliably show right away, maybe due to post-connection
  # close DeferredUpdates that could still be finishing up when next page load starts.
  sleep 5

  visit(RecentChangesPage)
end

Then(/^the new topic should be in the Recent Changes page$/) do
  step 'I navigate to the Recent Changes page'

  step "page has no ResourceLoader errors"
  expect(on(RecentChangesPage).recent_changes_element.when_present.text).to match @topic_string
end

Then(/^the new title should be in the Recent Changes page$/) do
  expect(on(RecentChangesPage).recent_changes_element.when_present.text).to match @edited_topic_string
end
