class TopicHistoryPage
  include PageObject

  div(:flow_topic_history, class: 'flow-topic-histories')

  ul(:flow_history_moderation, class: 'flow-history-moderation-menu')

  link(:undo_link) do
    flow_history_moderation_element.link_element(text: /undo/)
  end
end
