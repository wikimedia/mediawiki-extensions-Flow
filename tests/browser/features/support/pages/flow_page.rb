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
  textarea(:comment_field, css: 'form.flow-topic-reply-form > textarea[name="topic_content"]')
  button(:comment_reply_save, css: "form.flow-topic-reply-form .flow-reply-submit")
  a(:edit_post, class: "flow-edit-post-link", index: topic_index)
  a(:edit_title_icon, css: "div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-edit-title > a.mw-ui-button.flow-edit-topic-link")
  div(:flow_topics, class: "flow-topics")
  div(:highlighted_comment, class: "flow-post-highlighted")

  # Posts
  ## Top post
  div(:flow_first_topic, css: ".flow-topic", index: 0)
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  div(:flow_first_topic_body, css: ".flow-post-content", index: 0)
  div(:flow_first_topic_summary) do |page|
    page.flow_first_topic_element.div_element(css: ".flow-topic-summary")
  end
  div(:flow_first_topic_moderated_message) do |page|
    page.flow_first_topic_element.div_element(css: ".flow-moderated-topic-title")
  end

  ### Hover over username behaviour
  a(:talk_link, css: "..flow-author:hover mw-usertoollinks a", index: 0)
  a(:block_user, css: ".flow-author:hover .mw-usertoollinks a", index: 1)

  # Collapse button
  a(:full_view, href: "#collapser/full")
  a(:small_view, href: "#collapser/compact")
  a(:collapsed_view, href: "#collapser/topics")

  # Topic actions menu
  div(:topic_titlebar, css: ".flow-topic .flow-topic-titlebar")
  a(:topic_actions_link, css: ".flow-topic .flow-topic-titlebar .flow-menu-js-drop a", index: 0)
  ## Menu
  ul(:topic_actions_menu, css: ".flow-topic .flow-topic-titlebar .flow-menu ul", index: 0)
  a(:topic_delete_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Hide topic")
  end
  a(:topic_hide_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Delete topic")
  end
  a(:topic_suppress_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Suppress topic")
  end
  a(:topic_close_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Close topic")
  end
  a(:topic_reopen_button) do |page|
    page.topic_actions_menu_element.link_element(title: "Reopen topic")
  end

  ## Close topic workflow
  form(:topic_close_form, css: ".flow-edit-form")
  textarea(:topic_close_form_reason, css: ".flow-edit-form textarea")
  button(:topic_close_form_close_button, css: ".flow-edit-form .flow-ui-constructive")
  button(:topic_close_form_cancel_button, css: ".flow-edit-form .flow-ui-destructive")

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

  # New topic creation
  form(:new_topic_form, css: ".flow-newtopic-form")
  text_field(:new_topic_title, name: "topiclist_topic")
  textarea(:new_topic_body, name: "topiclist_content")
  button(:new_topic_cancel, css: ".flow-newtopic-form .flow-ui-destructive")
  button(:new_topic_preview, css: ".flow-newtopic-form .flow-ui-progressive")
  button(:new_topic_save, css: ".flow-newtopic-form .flow-ui-constructive")
  a(:permalink, css: "div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-permalink > a.mw-ui-button.flow-action-permalink-link")

  # Replying
  form(:new_reply_form, css: ".flow-reply-form")
  button(:new_reply_cancel, css: ".flow-reply-form .flow-ui-destructive")
  button(:new_reply_preview, css: ".flow-reply-form .flow-ui-progressive")
  button(:new_reply_save, css: ".flow-reply-form .flow-ui-constructive")

  text_area(:post_edit, css: "form.flow-edit-form .flow-edit-content")
  button(:preview_button, class: "mw-ui-button flow-preview-submit")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")
  text_field(:title_edit, css: "form.flow-edit-title-form .flow-edit-content")
  div(:topic_post, class: "flow-post-content", index: topic_index)
  div(:topic_title, class: "flow-topic-title", index: topic_index)

  button(:edit_header_save, text: "Save header")
end
