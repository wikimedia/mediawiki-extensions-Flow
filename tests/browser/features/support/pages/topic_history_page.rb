class TopicHistoryPage
  include PageObject

  include URL

  page_url URL.url("Topic:?action=history")

  div(:flow_topic_history, class: 'flow-topic-histories')
end
