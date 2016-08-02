class SpecialNotificationsPage
  include PageObject

  page_url "Special:Notifications"

  div(:first_notification, class: 'mw-echo-ui-notificationItemWidget', index: 0)
end
