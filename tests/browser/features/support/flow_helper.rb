module FlowHelper
  def create_topic_on(page, topic, content)
    api.action('flow',
               token_type: 'edit',
               submodule: 'new-topic',
               page: page,
               nttopic: topic,
               ntcontent: content)
  end
end
