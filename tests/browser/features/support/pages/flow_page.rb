class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_save, css: ".flow-newtopic-form .mw-ui-constructive, .flow-newtopic-form .flow-ui-constructive")
  text_field(:new_topic_title, name: "topiclist_topic")
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  div(:flow_second_topic_heading, css: ".flow-topic", index: 1)
  div(:highlighted_post, css: ".flow-post-highlighted")

  # Elements belonging to the first topic to be nested under flow_first_topic
  # See I have created a Flow topic with title steps for experiment with getting the id of the first topic
  div(:flow_first_topic, css: ".flow-topic", index: 0)
  a(:author_link)              { |page| page.flow_first_topic_element.link_element(css: ".flow-author a") }
  textarea(:new_reply_input)   { |page| page.flow_first_topic_element.link_element(css: ".flow-reply-form .mw-ui-input") }
  button(:new_reply_save)      { |page| page.flow_first_topic_element.button_element(css: ".flow-reply-form .mw-ui-constructive") }
  a(:permalink_button)         { |page| page.flow_first_topic_element.link_element(text: "Permalink") }
  a(:third_post_actions_link)  { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 3) }
  ul(:third_post_actions_menu) { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post .flow-menu ul", index: 3) }
  a(:topic_actions_link)       { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-topic-titlebar .flow-menu-js-drop a") }
  span(:usertools)             { |page| page.flow_first_topic_element.link_element(css: '.mw-usertoollinks') }

  # Elements belonging to the third post nested inside the first topic
  a(:actions_link_permalink_3rd_comment) { |page| page.third_post_actions_menu_element.link_element(text: "Permalink") }

  # Elements in usertools inside the first topic
  a(:usertools_talk_link) { |page| page.usertools_element.link_element(text: 'Talk') }
  a(:usertools_block_user_link) { |page| page.usertools_element.link_element(text: 'block') }
end
