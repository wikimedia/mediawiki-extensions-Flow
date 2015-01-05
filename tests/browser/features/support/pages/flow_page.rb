class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_save, css: ".flow-newtopic-form .mw-ui-constructive, .flow-newtopic-form .flow-ui-constructive")
  text_field(:new_topic_title, name: "topiclist_topic")

  #Elements belonging to the first topic to be nested under flow_first_topic
  div(:flow_first_topic, css: ".flow-topic", index: 0)
  a(:topic_actions_link) do |page|
    page.flow_first_topic_element.link_element(css: ".flow-topic .flow-topic-titlebar .flow-menu-js-drop a")
  end



  end
