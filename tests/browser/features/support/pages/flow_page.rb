class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  div(:dialog, css: ".flow-ui-modal")
  textarea(:dialog_input, name: "topic_reason")
  button(:dialog_cancel, css: "a.mw-ui-destructive:nth-child(2)")
  button(:dialog_submit_delete, text: "Delete")
  #button(:dialog_submit_hide, text: "Hide")
  button(:dialog_submit_suppress, text: "Suppress")
  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_save, css: ".flow-newtopic-form .mw-ui-constructive, .flow-newtopic-form .flow-ui-constructive")
  text_field(:new_topic_title, name: "topiclist_topic")
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  div(:flow_second_topic_heading, css: ".flow-topic", index: 1)
  div(:highlighted_post, css: ".flow-post-highlighted")
  form(:topic_lock_form, css: ".flow-edit-form")
  textarea(:topic_lock_form_reason, css: ".flow-edit-form textarea")
  button(:topic_lock_form_lock_button, css: ".flow-edit-form .mw-ui-constructive")
  button(:topic_lock_form_cancel_button, css: ".flow-edit-form .mw-ui-destructive")
  div(:flow_reason, class: "flow-moderated-topic-reason")

  # Elements belonging to the first topic to be nested under flow_first_topic
  div(:flow_first_topic, css: ".flow-topic", index: 0)
  a(:author_link)                          { |page| page.flow_first_topic_element.link_element(css: ".flow-author a") }
  button(:change_post_save)                { |page| page.flow_first_topic_element.link_element(css: ".flow-edit-post-form .mw-ui-constructive") }
  button(:change_title_save)               { |page| page.flow_first_topic_element.link_element(css: ".flow-topic-titlebar form .mw-ui-constructive") }
  div(:flow_first_topic_body)              { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post-content") }
  div(:flow_first_topic_moderation_msg)    { |page| page.flow_first_topic_element.link_element(css: 'div.flow-topic-titlebar div.flow-moderated-topic-title') }
  a(:flow_first_topic_original_post_edit)  { |page| page.flow_first_topic_element.link_element(text: 'Edit') }
  a(:flow_first_topic_original_post_reply) { |page| page.flow_first_topic_element.link_element(text: 'Reply') }
  textarea(:new_reply_input)               { |page| page.flow_first_topic_element.link_element(css: ".flow-reply-form .mw-ui-input") }
  button(:new_reply_save)                  { |page| page.flow_first_topic_element.button_element(css: ".flow-reply-form .mw-ui-constructive") }
  a(:permalink_button)                     { |page| page.flow_first_topic_element.link_element(text: "Permalink") }
  a(:post_actions_link)                    { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post .flow-menu-js-drop a") }
  ul(:post_actions_menu)                   { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post .flow-menu ul") }
  text_area(:post_edit)                    { |page| page.flow_first_topic_element.link_element(css: ".flow-edit-post-form textarea") }
  span(:post_meta_actions)                 { |page| page.flow_first_topic_element.link_element(css: ".flow-post .flow-post-meta-actions") }
  a(:third_post_actions_link)              { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 3) }
  ul(:third_post_actions_menu)             { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-post .flow-menu ul", index: 3) }
  text_field(:title_edit)                  { |page| page.flow_first_topic_element.link_element(css: ".flow-topic-titlebar form .mw-ui-input") }
  a(:topic_actions_link)                   { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-topic-titlebar .flow-menu-js-drop a") }
  ul(:topic_actions_menu)                  { |page| page.flow_first_topic_element.link_element(css: ".flow-topic .flow-topic-titlebar .flow-menu ul") }
  a(:topic_delete_button)                  { |page| page.flow_first_topic_element.link_element(text: 'Delete topic') }
  a(:topic_hide_button)                    { |page| page.flow_first_topic_element.link_element(text: 'Hide topic') }
  a(:topic_lock_button)                    { |page| page.flow_first_topic_element.link_element(title: 'Lock topic') }
  a(:topic_suppress_button)                { |page| page.flow_first_topic_element.link_element(text: 'Suppress topic') }
  a(:topic_unlock_button)                  { |page| page.flow_first_topic_element.link_element(title: 'Unlock topic') }
  span(:usertools)                         { |page| page.flow_first_topic_element.link_element(css: '.mw-usertoollinks') }

  # Elements belonging to the meta actions for the first topic
  a(:edit_post)         { |page| page.post_meta_actions_element.link_element(title: "Edit") }
  a(:thank_button)      { |page| page.post_meta_actions_element.link_element(css: ".mw-thanks-flow-thank-link") }
  span(:thanked_button) { |page| page.post_meta_actions_element.span_element(css: ".mw-thanks-flow-thanked") }

  # Elements belonging to the third post nested inside the first topic
  a(:actions_link_permalink_3rd_comment) { |page| page.third_post_actions_menu_element.link_element(text: "Permalink") }

  # Elements belonging to the topic actions menu for the first topic
  a(:edit_title_button) { |page| page.topic_actions_menu_element.link_element(text: "Edit title") }

  # Elements belonging to the post actions for the first post in the topic
  a(:hide_button)     { |page| page.post_actions_menu_element.link_element(title: "Hide") }
  a(:delete_button)   { |page| page.post_actions_menu_element.link_element(title: "Delete") }
  a(:suppress_button) { |page| page.post_actions_menu_element.link_element(title: "Suppress") }

  # Elements in usertools inside the first topic
  a(:usertools_talk_link)       { |page| page.usertools_element.link_element(text: 'Talk') }
  a(:usertools_block_user_link) { |page| page.usertools_element.link_element(text: 'block') }

  # No javascript elements
  button(:no_javascript_add_topic, text: "Add topic")
  div(:no_javascript_page_content_body, class: "flow-post-content")
  div(:no_javascript_page_content_title, class: "flow-topic-titlebar")
  div(:no_javascript_page_flow_topics, class: "flow-topics")
  button(:no_javascript_reply, text: "Reply")
  textarea(:no_javascript_reply_form, name: "topic_content")
  a(:no_javascript_start_reply, href: /action=reply/)
  a(:no_javascript_start_topic, href: /action=new-topic/)
  textarea(:no_javascript_topic_body_text, name: "topiclist_content")
  text_field(:no_javascript_topic_title_text, name: "topiclist_topic")
end
