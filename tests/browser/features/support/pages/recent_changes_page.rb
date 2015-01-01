class RecentChangesPage
  include PageObject

  include URL
  page_url URL.url('Special:RecentChanges')

  div(:recent_changes, class: 'mw-changeslist')
end
