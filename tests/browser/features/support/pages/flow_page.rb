class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  # This hack makes Chrome edit the second topic on the page to avoid edit
  # conflicts from simultaneous test runs (bug 59011).
  if ENV['BROWSER'] == "chrome"
    topic_index = 1
    actions_index = 2
  else
    topic_index = 0
    actions_index = 0
  end

  # board header
  a(:edit_header_link, title: "Edit header")
  div(:header_content, css: ".flow-board-header-detail-view p", index: 0)
  form(:edit_header_form, css: ".flow-board-header-edit-view form")
  textarea(:edit_header_textbox, css: ".flow-board-header-edit-view textarea")

  a(:actions_link_permalink_3rd_comment, text: "Actions", index: 4)
  a(:author_link, css: ".flow-author a")
  a(:cancel_button, text: "Cancel")
  button(:change_post_save, css: "form.flow-edit-form .flow-edit-submit")
  button(:change_title_save, css: "form.flow-edit-title-form .flow-edit-submit")

  # XXX (mattflaschen, 2014-06-24): This is broken; there is no
  # flow-topic-reply-form anywhere in Flow outside this file.
  # Also, this should be named to distinguish between top-level posts and regular replies.
  textarea(:comment_field, css: 'form.flow-topic-reply-form > textarea[name="topic_content"]')
  button(:comment_reply_save, css: "form.flow-topic-reply-form .flow-reply-submit")
  a(:edit_post, class: "flow-edit-post-link", index: topic_index)
  a(:edit_title_icon, css: "div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-edit-title > a.mw-ui-button.flow-edit-topic-link")
  div(:flow_topics, class: "flow-topics")
  div(:highlighted_comment, class: "flow-post-highlighted")

  # Posts
  ## Top post
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  div(:flow_first_topic_body, css: ".flow-post-content", index: 0)

  ### Hover over username behaviour
  a(:talk_link, css: "..flow-author:hover mw-usertoollinks a", index: 0)
  a(:block_user, css: ".flow-author:hover .mw-usertoollinks a", index: 1)

  # Collapse button
  a(:topics_and_posts_view, href: "#collapser/full")
  a(:small_topics_view, href: "#collapser/compact")
  a(:topics_only_view, href: "#collapser/topics")

  # For topic collapsing testing
  # Watir WebDriver apparently doesn't support CSS :not (https://developer.mozilla.org/en-US/docs/Web/CSS/:not), so using XPath
  h2(:first_non_moderated_topic_title, xpath: '(//*[contains(@class, "flow-topic ") and not(contains(@class, "flow-topic-moderated"))]//h2[contains(@class, "flow-topic-title")])[1]')
  span(:first_non_moderated_topic_starter, xpath: '(//*[contains(@class, "flow-topic ") and not(contains(@class, "flow-topic-moderated"))]//*[contains(@class, "flow-topic-titlebar")]//*[contains(@class, "flow-author")])[1]')
  div(:first_non_moderated_topic_post_content, xpath: '(//*[contains(@class, "flow-topic ") and not(contains(@class, "flow-topic-moderated"))]//*[contains(@class, "flow-post-content")])[1]')

  # Topic actions menu (all belonging to the first post)
  a(:topic_actions_link, css: ".flow-topic .flow-topic-titlebar .flow-menu-js-drop a", index: 0)
  ## Menu
  ul(:topic_actions_menu, css: ".flow-topic .flow-topic-titlebar .flow-menu ul", index: 0)
  a(:topic_delete_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Delete topic")
  end
  a(:topic_hide_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Hide topic")
  end
  a(:topic_suppress_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Suppress topic")
  end

  # Post actions menu
  a(:post_actions_link, css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 0)
  ## Menu
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

  # Hiding a topic with no-JS; may also be applicable for other moderation
  textarea(:topic_reason, name: "topic_reason")
  button(:topic_submit, css: '.flow-form-actions button[data-role="submit"]')

  # New topic creation
  form(:new_topic_form, css: ".flow-newtopic-form")
  text_field(:new_topic_title, name: "topiclist_topic")
  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_cancel, css: ".flow-newtopic-form .flow-ui-destructive")
  button(:new_topic_preview, css: ".flow-newtopic-form .flow-ui-progressive")
  button(:new_topic_save, css: ".flow-newtopic-form .flow-ui-constructive")
  a(:permalink, css: "div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-permalink > a.mw-ui-button.flow-action-permalink-link")

  # Replying
  # TODO (mattflaschen, 2014-06-24): Should distinguish between
  # top-level replies to the topic, and replies to regular posts
  form(:new_reply_form, css: ".flow-reply-form")
  button(:new_reply_cancel, css: ".flow-reply-form .flow-ui-destructive")
  button(:new_reply_preview, css: ".flow-reply-form .flow-ui-progressive")
  button(:new_reply_save, css: ".flow-reply-form .flow-ui-constructive")
  text_field(:new_reply_text_unexpanded, css: '.flow-reply-form input[name="topic_content"]')
  textarea(:new_reply_text_expanded, css: '.flow-reply-form textarea[name="topic_content"]')

  text_area(:post_edit, css: "form.flow-edit-form .flow-edit-content")
  button(:preview_button, class: "mw-ui-button flow-preview-submit")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")
  text_field(:title_edit, css: "form.flow-edit-title-form .flow-edit-content")
  div(:topic_post, class: "flow-post-content", index: topic_index)
  div(:topic_title, class: "flow-topic-title", index: topic_index)

  button(:edit_header_save, text: "Save header")
end
