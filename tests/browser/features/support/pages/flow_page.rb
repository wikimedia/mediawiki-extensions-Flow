class WikiPage
  include PageObject
  a(:logout, css: "#pt-logout a")
end

class FlowPage < WikiPage
  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  # board header
  a(:edit_header_link, title: "Edit header")
  div(:header_content, css: ".flow-board-header-detail-view p", index: 0)
  form(:edit_header_form, css: ".flow-board-header-edit-view form")
  textarea(:edit_header_textbox, css: ".flow-board-header-edit-view textarea")

  a(:author_link, css: ".flow-author a", index: 0)
  a(:cancel_button, text: "Cancel")

  # XXX (mattflaschen, 2014-06-24): This is broken; there is no
  # flow-topic-reply-form anywhere in Flow outside this file.
  # Also, this should be named to distinguish between top-level posts and regular replies.
  textarea(:comment_field, css: 'form.flow-topic-reply-form > textarea[name="topic_content"]')
  button(:comment_reply_save, css: "form.flow-topic-reply-form .flow-reply-submit")
  div(:flow_topics, class: "flow-topics")

  # Dialogs
  div(:dialog, css: ".flow-ui-modal")
  textarea(:dialog_input, name: "topic_reason")
  button(:dialog_cancel, css: "a.mw-ui-destructive:nth-child(2)")
  button(:dialog_submit_delete, text: "Delete")
  button(:dialog_submit_hide, text: "Hide")
  button(:dialog_submit_suppress, text: "Suppress")

  # Posts
  ## Highlighted post
  div(:highlighted_post, css: ".flow-post-highlighted")

  ## First topic
  div(:flow_first_topic, css: ".flow-topic", index: 0)
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  # todo this is poor naming, it's really the first_topic_first_post_content
  div(:flow_first_topic_body, css: ".flow-topic .flow-post-content", index: 0)
  div(:flow_first_topic_moderation_msg) do |page|
    page.flow_first_topic_element.div_element(css: "div.flow-topic-titlebar div.flow-moderated-topic-title")
  end

  div(:flow_first_topic_summary) do |page|
    page.flow_first_topic_element.div_element(css: ".flow-topic-summary")
  end
  div(:flow_first_topic_original_post, css: ".flow-post", index: 0)
  a(:flow_first_topic_original_post_edit) do |page|
    page.flow_first_topic_original_post_element.link_element(text: "Edit")
  end
  a(:flow_first_topic_original_post_reply) do |page|
    page.flow_first_topic_original_post_element.link_element(text: "Reply")
  end
  div(:flow_second_topic_heading, css: ".flow-topic", index: 1)

  ### Hover over username behaviour
  span(:usertools, css: '.mw-usertoollinks')
  a(:usertools_talk_link) do |page|
    page.usertools_element.link_element(text: 'Talk')
  end
  a(:usertools_block_user_link) do |page|
    page.usertools_element.link_element(text: 'block')
  end

  ### First Topic actions menu

  # For topic collapsing testing
  # Works around CSS descendant selector problem (https://github.com/cheezy/page-object/issues/222)
  div(:first_moderated_topic, css: '.flow-topic.flow-topic-moderated', index: 0)

  div(:first_moderated_topic_titlebar) do |page|
    page.first_moderated_topic_element.div_element(css: '.flow-topic-titlebar')
  end

  div(:first_moderated_message) do |page|
    page.first_moderated_topic_titlebar_element.div_element(css: '.flow-moderated-topic-title')
  end

  h2(:first_moderated_topic_title) do |page|
    page.first_moderated_topic_titlebar_element.h2_element(class: 'flow-topic-title')
  end

  div(:first_moderated_topic_post_content) do |page|
    page.first_moderated_topic_element.div_element(class: 'flow-post', index: 0).div_element(class: 'flow-post-main').div_element(class: 'flow-post-content')
  end

  # Topic actions menu (all belonging to the first post)
  a(:topic_actions_link, css: ".flow-topic .flow-topic-titlebar .flow-menu-js-drop a", index: 0)
  ul(:topic_actions_menu, css: ".flow-topic .flow-topic-titlebar .flow-menu ul", index: 0)
  a(:topic_hide_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Hide topic")
  end
  a(:topic_delete_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Delete topic")
  end
  a(:topic_suppress_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Suppress topic")
  end
  a(:permalink_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Permalink")
  end
  a(:edit_title_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Edit title")
  end
  a(:topic_lock_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Lock topic")
  end
  a(:topic_unlock_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Unlock topic")
  end

  ## Lock topic workflow
  form(:topic_lock_form, css: ".flow-edit-form")
  textarea(:topic_lock_form_reason, css: ".flow-edit-form textarea")
  button(:topic_lock_form_lock_button, css: ".flow-edit-form .mw-ui-constructive")
  button(:topic_lock_form_cancel_button, css: ".flow-edit-form .mw-ui-destructive")
  div(:flow_reason, class: "flow-moderated-topic-reason")

  ### Editing title of first topic
  text_field(:title_edit, css: ".flow-topic-titlebar form .mw-ui-input", index: 0)
  button(:change_title_save, css: ".flow-topic-titlebar form .mw-ui-constructive")

  ### Post meta actions
  span(:post_meta_actions, css: ".flow-post .flow-post-meta-actions", index: 0)
  a(:edit_post) do |page|
    page.post_meta_actions_element.link_element(title: "Edit")
  end
  a(:thank_button) do |page|
    page.post_meta_actions_element.link_element(css: ".mw-thanks-flow-thank-link", index: 0)
  end
  span(:thanked_button) do |page|
    page.post_meta_actions_element.span_element(css: ".mw-thanks-flow-thanked", index: 0)
  end

  ### First post of first topic actions menu
  a(:post_actions_link, css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 0)
  ul(:post_actions_menu, css: ".flow-topic .flow-post .flow-menu ul", index: 0)
  a(:hide_button) do |page|
    page.post_actions_menu_element.link_element(title: "Hide")
  end
  a(:delete_button) do |page|
    page.post_actions_menu_element.link_element(title: "Delete")
  end
  a(:suppress_button) do |page|
    page.post_actions_menu_element.link_element(title: "Suppress")
  end

  ### Replies to top post
  #### 1st reply
  # @todo: This is broken. It should be clearly possible to distinguish between the top reply and
  # the top post. There is an element .flow-replies which appears to be empty.
  div(:first_reply, css: '.flow-post', index: 1)
  div(:first_reply_body) do |page|
    page.first_reply_element.div_element(css: '.flow-post-content')
  end

  #### 3rd reply
  # @todo: Should be index: 2, but sadly no way to distinguish replies from original post
  div(:third_reply, css: '.flow-post', index: 3)
  div(:third_reply_moderation_msg) do |page|
    page.third_reply_element.span_element(css: '.flow-moderated-post-content', index: 0)
  end
  div(:third_reply_content) do |page|
    page.third_reply_element.div_element(css: '.flow-post-content', index: 0)
  end

  a(:third_post_actions_link, css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 3)
  ul(:third_post_actions_menu, css: ".flow-topic .flow-post .flow-menu ul", index: 3)
  a(:actions_link_permalink_3rd_comment) do |page|
    page.third_post_actions_menu_element.link_element(text: "Permalink")
  end
  a(:actions_link_hide_3rd_comment) do |page|
    page.third_post_actions_menu_element.link_element(text: "Hide")
  end

  # New topic creation
  form(:new_topic_form, css: ".flow-newtopic-form")
  text_field(:new_topic_title, name: "topiclist_topic")
  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_cancel, css: ".flow-newtopic-form .mw-ui-destructive")
  button(:new_topic_preview, css: ".flow-newtopic-form .mw-ui-progressive")
  # FIXME: Remove flow-ui-constructive reference when cache has cleared
  button(:new_topic_save, css: ".flow-newtopic-form .mw-ui-constructive, .flow-newtopic-form .flow-ui-constructive")

  # Replying
  # TODO (mattflaschen, 2014-06-24): Should distinguish between
  # top-level replies to the topic, and replies to regular posts
  form(:new_reply_form, css: ".flow-reply-form")
  # Is an input when not focused, textarea when focused
  textarea(:new_reply_input, css: ".flow-reply-form .mw-ui-input")
  button(:new_reply_cancel, css: ".flow-reply-form .mw-ui-destructive")
  button(:new_reply_preview, css: ".flow-reply-form .mw-ui-progressive")
  button(:new_reply_save, css: ".flow-reply-form .mw-ui-constructive")
  button(:keep_editing, text: "Keep editing")
  div(:preview_warning, css: ".flow-preview-warning")

  # Editing post workflow
  text_area(:post_edit, css: ".flow-edit-post-form textarea")
  button(:change_post_save, css: ".flow-edit-post-form .mw-ui-constructive")

  button(:preview_button, class: "mw-ui-button flow-preview-submit")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")

  button(:edit_header_save, text: "Save header")

  # No javascript elements
  button(:no_javascript_add_topic, text: "Add topic")
  div(:no_javascript_page_content_body, class: "flow-post-content")
  div(:no_javascript_page_content_title, class: "flow-topic-titlebar flow-click-interactive")
  div(:no_javascript_page_flow_topics, class: "flow-topics")
  button(:no_javascript_reply, text: "Reply")
  textarea(:no_javascript_reply_form, name: "topic_content")
  a(:no_javascript_start_reply, href: /action=reply/)
  a(:no_javascript_start_topic, href: /action=new-topic/)
  textarea(:no_javascript_topic_body_text, name: "topiclist_content")
  text_field(:no_javascript_topic_title_text, name: "topiclist_topic")

  # Sorting
  a(:newest_topics_link, text: "Newest topics")
  a(:recently_active_topics_choice, href: /topiclist_sortby=updated/)
  a(:recently_active_topics_link, text: "Recently active topics")
  a(:newest_topics_choice, href: /topiclist_sortby=newest/)

  ## Watch and unwatch links
  div(:first_topic_watchlist_container, css: ".flow-topic-watchlist", index: 0)
  a(:first_topic_watch_link) do |page|
    page.first_topic_watchlist_container_element.link_element(css: ".flow-watch-link-watch")
  end
  a(:first_topic_unwatch_link) do |page|
    page.first_topic_watchlist_container_element.link_element(css: ".flow-watch-link-unwatch")
  end

  a(:board_unwatch_link, href: /Flow_QA&action=unwatch/)
  a(:board_watch_link, href: /Flow_QA&action=watch/)
end
