class RecentChangesPage
  include PageObject

  page_url 'Special:RecentChanges'

  div(:recent_changes, class: 'mw-changeslist')
end
