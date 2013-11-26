require "page-object"

class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  span(:author_link, class: "flow-creator-simple")
  a(:talk_link, text: "Talk")
  a(:contrib_link, text: "contribs")
  text_field(:new_topic_title, name: "topic_list[topic]")
  text_area(:new_topic_body, class: "flow-newtopic-content")
  button(:new_topic_save, class: "flow-newtopic-submit")
  div(:flow_body, class: "flow-container")
end
