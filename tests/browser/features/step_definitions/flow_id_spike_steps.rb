Then(/^the topic should be visible with an id$/) do
  #expect(on(FlowSpikePage).topic_spike_element.when_present).to be_visible
  expect(on(FlowSpikePage { |page| page.all_the_topics_element.div_element(id: "flow-topic-#{@topic_id_value}") })).to be_visible
end

