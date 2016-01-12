class AbstractFlowPage
  include PageObject
  include FlowEditor

  page_section(:description, BoardDescription, class: 'flow-board-header')

  def select_menu_option(menu, option)
    menu.when_present.click
    wait_until { option.exists? }
    option.scroll_into_view
    menu.when_present.click
    option.when_present.click
  end

  a(:logout, css: "#pt-logout a")

  # board component
  div(:flow_component, class: 'flow-component')
  div(:flow_board, class: 'flow-board')

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
  text_field(:dialog_input, name: "topic_reason")
  button(:dialog_cancel, css: "a.mw-ui-destructive:nth-child(2)")
  button(:dialog_submit_delete, text: "Delete")
  button(:dialog_submit_hide, text: "Hide")
  button(:dialog_submit_suppress, text: "Suppress")

  # Posts
  ## Highlighted post
  div(:highlighted_post, css: ".flow-post-highlighted")

  def topic_with_title(title)
    h2_element(text: title)
  end

  ## First topic
  div(:flow_first_topic, css: ".flow-topic", index: 0)
  h2(:flow_first_topic_heading, css: ".flow-topic h2", index: 0)
  # todo this is poor naming, it's really the first_topic_first_post_content
  div(:flow_first_topic_body) do
    div_element(class: "flow-topic", index: 0).div_element(class: 'flow-post-content')
  end
  div(:flow_first_topic_moderation_msg) do |page|
    page.flow_first_topic_element.div_element(css: "div.flow-topic-titlebar div.flow-moderated-topic-title")
  end

  ## First post with link HTML in topic title
  a(:flow_first_topic_main_page_link) do
    h2_element(css: ".flow-topic-title", index: 0).link_element(href: %r{/wiki/Main_Page})
  end

  a(:flow_first_topic_red_link) do
    h2_element(css: ".flow-topic-title", index: 0).link_element(class: 'new')
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
    page.usertools_element.link_element(text: 'talk')
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
  a(:topic_history_button) do |page|
    page.topic_actions_menu_element.link_element(text: "History")
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
  a(:topic_resolve_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Mark as resolved")
  end
  a(:topic_reopen_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Reopen topic")
  end
  a(:topic_summarize_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Summarize")
  end
  a(:topic_edit_summary_button) do |page|
    page.topic_actions_menu_element.link_element(text: "Edit the topic summary")
  end

  ### Editing title of first topic
  text_field(:title_edit, css: ".flow-ui-topicTitleWidget-titleInput input", index: 0)
  a(:change_title_save, css: ".flow-ui-topicTitleWidget-saveButton a")

  ### Post meta actions
  span(:post_meta_actions, css: ".flow-post .flow-post-meta-actions", index: 0)
  a(:thank_button) do |page|
    page.post_meta_actions_element.link_element(css: ".mw-thanks-flow-thank-link", index: 0)
  end
  span(:thanked_button) do |page|
    page.post_meta_actions_element.span_element(css: ".mw-thanks-flow-thanked", index: 0)
  end

  ### summary of first topic
  div(:summary) do |page|
    page.flow_first_topic_element.div_element(css: '.flow-topic-summary')
  end
  div(:summary_content) do |page|
    page.summary_element.div_element(css: '.flow-topic-summary-content')
  end
  link(:skip_summary_button, text: 'Skip summary')
  link(:cancel_summary_button) do |page|
    page.summary_element.link_element(text: 'Cancel')
  end
  link(:update_summary_button, text: 'Update summary')
  def edit_summary_element
    edit_summary_widget = div_element(class: 'flow-ui-editTopicSummaryWidget')
    visualeditor_or_textarea edit_summary_widget
  end
  span(:first_topic_resolved_mark) do |page|
    page.flow_first_topic_heading_element.span_element(css: '.mw-ui-icon-check')
  end

  ### First post of first topic actions menu
  a(:post_actions_link, css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 0)
  ul(:post_actions_menu, css: ".flow-topic .flow-post .flow-menu ul", index: 0)
  a(:permalink_button) do |page|
    page.post_actions_menu_element.link_element(text: "Permalink")
  end
  a(:hide_button) do |page|
    page.post_actions_menu_element.link_element(text: "Hide")
  end
  a(:delete_button) do |page|
    page.post_actions_menu_element.link_element(text: "Delete")
  end
  a(:suppress_button) do |page|
    page.post_actions_menu_element.link_element(text: "Suppress")
  end
  a(:edit_post_button) do |page|
    page.post_actions_menu_element.link_element(text: "Edit")
  end

  ### Replies to top post
  #### 1st reply
  # @todo: This is broken. It should be clearly possible to distinguish between the top reply and
  # the top post. There is an element .flow-replies which appears to be empty.
  div(:first_reply, css: '.flow-post', index: 1)
  div(:first_reply_body) do |page|
    page.first_reply_element.div_element(css: '.flow-post-content')
  end

  #### 2rd post
  div(:second_post, css: '.flow-post', index: 1)
  a(:second_post_actions_link, css: ".flow-topic .flow-post .flow-menu-js-drop a", index: 1)
  ul(:second_post_actions_menu, css: ".flow-topic .flow-post .flow-menu ul", index: 1)

  a(:actions_link_permalink_second_comment) do |page|
    page.second_post_actions_menu_element.link_element(text: "Permalink")
  end

  a(:actions_link_hide_second_comment) do |page|
    page.second_post_actions_menu_element.link_element(text: "Hide")
  end

  div(:second_post_content) do |page|
    page.second_post_element.div_element(css: '.flow-post-content', index: 0)
  end
  div(:second_post_moderation_msg) do |page|
    page.second_post_element.span_element(css: '.flow-moderated-post-content', index: 0)
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
  a(:new_topic_link, text: "Start a new topic")
  form(:new_topic_form, css: ".flow-newtopic-form")
  text_field(:new_topic_title, css: ".flow-ui-newTopicWidget-title > input")

  div(:new_topic_widget, class: 'flow-ui-newTopicWidget')
  def new_topic_body_element
    visualeditor_or_textarea new_topic_widget_element
  end

  link(:new_topic_cancel) do
    new_topic_widget_element.link_element(text: 'Cancel')
  end

  link(:new_topic_save) do
    new_topic_widget_element.link_element(text: /Add topic.*/)
  end

  # Replying
  # TODO (mattflaschen, 2014-06-24): Should distinguish between
  # top-level replies to the topic, and replies to regular posts
  form(:new_reply_form, css: ".flow-reply-form")

  div(:first_reply_widget) do
    flow_first_topic_element.div_element(class: 'flow-ui-replyWidget')
  end

  div(:first_reply_placeholder) do
    first_reply_widget_element.text_field_element
  end

  def new_reply_editor_element
    visualeditor_or_textarea first_reply_widget_element
  end

  link(:new_reply_cancel) do
    first_reply_widget_element.link_element(text: 'Cancel')
  end
  link(:new_reply_save) do
    first_reply_widget_element.link_element(text: /Reply.*/)
  end

  button(:keep_editing, text: "Keep editing")

  # Editing post workflow

  div(:edit_post_widget, class: 'flow-ui-editPostWidget')
  def post_edit_element
    visualeditor_or_textarea edit_post_widget_element
  end

  button(:change_post_save) do
    edit_post_widget_element.link_element(text: 'Save changes')
  end

  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")

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

  # Sorting
  div(:sorting, class: 'flow-ui-reorderTopicsWidget')
  link(:newest_topics_link, text: "Newest topics")
  link(:recently_active_topics_link, text: "Recently active topics")
  span(:newest_topics_choice, text: "Newest topics")
  span(:recently_active_topics_choice, text: "Recently active topics")

  ## Watch and unwatch links
  div(:first_topic_watchlist_container, css: ".flow-topic-watchlist", index: 0)
  a(:first_topic_watch_link) do |page|
    page.first_topic_watchlist_container_element.link_element(css: ".flow-watch-link-watch")
  end
  a(:first_topic_unwatch_link) do |page|
    page.first_topic_watchlist_container_element.link_element(css: ".flow-watch-link-unwatch")
  end

  a(:board_unwatch_link, css: '#ca-unwatch a')
  a(:board_watch_link, css: '#ca-watch a')

  # undo suppression
  button(:undo_suppression_button, text: "Undo")

  # history
  a(:view_history, text: 'View history')

  div(:overlay, class: 'flow-ui-load-overlay')
end
