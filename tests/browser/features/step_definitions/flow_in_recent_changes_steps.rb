When(/^I navigate to the Recent Changes page$/) do
  visit(RecentChangesPage)
end

Then(/^the new topic should be in the Recent Changes page$/) do
  on(RecentChangesPage) do |page|
    page.refresh_until { page.recent_changes.match @topic_string }
  end
end

Then(/^the new title should be in the Recent Changes page$/) do
  on(RecentChangesPage) do |page|
    page.refresh_until { page.recent_changes.match @edited_topic_string }
  end
end
