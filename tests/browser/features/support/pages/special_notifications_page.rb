class SpecialNotificationsPage
  include PageObject

  page_url "Special:Notifications"

  div(:first_notification, class: 'mw-echo-content', index: 0)
end
