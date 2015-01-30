class FlowSpikePage
  include PageObject

  div(:all_the_topics, class: 'flow-topics')
  div (:topic_spike) { |page| page.all_the_topics_element.div_element(id: "flow-topic-#{@topic_id_value}") }
end