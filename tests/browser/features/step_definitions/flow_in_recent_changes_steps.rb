When(/^I navigate to the Recent Changes page$/) do
  visit(RecentChangesPage)
end

Then(/^the new topic should be in the Recent Changes page$/) do
  on(RecentChangesPage) do |page|
    page.refresh_until { page.recent_changes.match @topic_string }
  end
  # expect(on(RecentChangesPage).recent_changes_element.when_present.text).to match @topic_string
end

Then(/^the new title should be in the Recent Changes page$/) do
  on(RecentChangesPage) do |page|
    page.refresh_until { page.recent_changes.match @edited_topic_string }
  end
  # expect(on(RecentChangesPage).recent_changes_element.when_present.text).to match @edited_topic_string
end
