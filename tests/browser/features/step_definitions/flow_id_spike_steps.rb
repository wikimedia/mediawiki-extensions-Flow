Then(/^the topic should be visible with an id$/) do
  expect(on(FlowSpikePage).topic_spike_element.when_present).to be_visible
end

