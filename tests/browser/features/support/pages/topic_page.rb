class TopicPage
  include PageObject

  include URL

  page_url URL.url("Topic:")

  # history
  a(:view_history, text: 'View history')
end
