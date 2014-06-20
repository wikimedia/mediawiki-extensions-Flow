class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  # board header
  a(:edit_header_link, title: "Edit header")
  div(:header_content, css: ".flow-board-header-detail-view p", index: 0)
  form(:edit_header_form, css: ".flow-board-header-edit-view form")
  textarea(:edit_header_textbox, css: ".flow-board-header-edit-view textarea")

  a(:author_link, css: ".flow-author a")
  a(:cancel_button, text: "Cancel")

  # XXX (mattflaschen, 2014-06-24): This is broken; there is no
  # flow-topic-reply-form anywhere in Flow outside this file.
  # Also, this should be named to distinguish between top-level posts and regular replies.
  textarea(:comment_field, css: 'form.flow-topic-reply-form > textarea[name="topic_content"]')
  button(:comment_reply_save, css: "form.flow-topic-reply-form .flow-reply-submit")
  div(:flow_topics, class: "flow-topics")

  # Collapse button
  a(:topics_and_posts_view, href: "#collapser/full")
  a(:small_topics_view, href: "#collapser/compact")
  a(:topics_only_view, href: "#collapser/topics")

  # Posts
  ## Highlighted post
  div(:highlighted_post, css: ".flow-post-highlighted")

  ## First topic
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  div(:flow_first_topic_body, css: ".flow-post-content", index: 0)
  div(:flow_first_topic_moderation_msg, css: '.flow-moderated-topic-title', index: 0)

  ### Hover over username behaviour
  a(:talk_link, css: "..flow-author:hover mw-usertoollinks a", index: 0)
  a(:block_user, css: ".flow-author:hover .mw-usertoollinks a", index: 1)

  ### First Topic actions menu
  # For topic collapsing testing
  # Watir WebDriver apparently doesn't support CSS :not (https://developer.mozilla.org/en-US/docs/Web/CSS/:not), so using XPath
  h2(:first_non_moderated_topic_title, xpath: '(//*[contains(@class, "flow-topic ") and not(contains(@class, "flow-topic-moderated"))]//h2[contains(@class, "flow-topic-title")])[1]')
  span(:first_non_moderated_topic_starter, xpath: '(//*[contains(@class, "flow-topic ") and not(contains(@class, "flow-topic-moderated"))]//*[contains(@class, "flow-topic-titlebar")]//*[contains(@class, "flow-author")])[1]')
  div(:first_non_moderated_topic_post_content, xpath: '(//*[contains(@class, "flow-topic ") and not(contains(@class, "flow-topic-moderated"))]//*[contains(@class, "flow-post-content")])[1]')

  # Works around CSS descendant selector problem (https://github.com/cheezy/page-object/issues/222)
  div(:first_moderated_topic, css: '.flow-topic.flow-topic-moderated', index: 0)

  div(:first_moderated_topic_titlebar) do |page|
    page.first_moderated_topic_element.div_element(class: 'flow-topic-titlebar')
  end

  h2(:first_moderated_topic_title) do |page|
    page.first_moderated_topic_titlebar_element.h2_element(class: 'flow-topic-title')
  end

  span(:first_moderated_topic_starter) do |page|
    page.first_moderated_topic_titlebar_element.span_element(class: 'flow-author')
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

  ### Editing title of first topic
  text_field(:title_edit, css: ".flow-topic-titlebar form .mw-ui-input", index: 0)
  button(:change_title_save, css: ".flow-topic-titlebar form .flow-ui-constructive")

  ### Post meta actions
  span(:post_meta_actions, css:".flow-post .flow-post-meta-actions", index: 0)
  a(:edit_post) do |page|
    page.post_meta_actions_element.link_element(title: "Edit")
  end

  ### Topic deletion workflow
  div(:dialog, css: ".ui-dialog")
  textarea(:dialog_input, css: ".ui-dialog textarea")
  button(:dialog_cancel, css: ".ui-dialog .flow-ui-destructive")
  button(:dialog_submit, css: ".ui-dialog .flow-ui-constructive")

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
  # @todo: This is broken. It should be clearly possible to distinguish between the top reply and
  # the top post. There is an element .flow-replies which appears to be empty.
  div(:first_reply, css: '.flow-post', index: 1)
  div(:first_reply_body) do |page|
    page.first_reply_element.div_element(css: '.flow-post-content')
  end
  a(:third_post_actions_link, css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 3)
  ul(:third_post_actions_menu, css: ".flow-topic .flow-post .flow-menu ul", index: 3)
  a(:actions_link_permalink_3rd_comment) do |page|
    page.third_post_actions_menu_element.link_element(text: "Permalink")
  end

  # New topic creation
  form(:new_topic_form, css: ".flow-newtopic-form")
  text_field(:new_topic_title, name: "topiclist_topic")
  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_cancel, css: ".flow-newtopic-form .flow-ui-destructive")
  button(:new_topic_preview, css: ".flow-newtopic-form .flow-ui-progressive")
  button(:new_topic_save, css: ".flow-newtopic-form .flow-ui-constructive")

  # Replying
  # TODO (mattflaschen, 2014-06-24): Should distinguish between
  # top-level replies to the topic, and replies to regular posts
  form(:new_reply_form, css: ".flow-reply-form")
  # Is an input when not focused, textarea when focused
  text_field(:new_reply_input, css: ".flow-reply-form .mw-ui-input")
  button(:new_reply_cancel, css: ".flow-reply-form .flow-ui-destructive")
  button(:new_reply_preview, css: ".flow-reply-form .flow-ui-progressive")
  button(:new_reply_save, css: ".flow-reply-form .flow-ui-constructive")

  # Editing post workflow
  text_area(:post_edit, css: ".flow-edit-post-form textarea")
  button(:change_post_save, css: ".flow-edit-post-form .flow-ui-constructive")

  button(:preview_button, class: "mw-ui-button flow-preview-submit")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")

  button(:edit_header_save, text: "Save header")
end
