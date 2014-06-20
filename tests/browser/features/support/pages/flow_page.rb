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

  a(:actions_link_permalink_3rd_comment, text: "Actions", index: 4)
  span(:author_link, class: "flow-creator")
  a(:block_user, title: /Special:Block/)
  a(:cancel_button, text: "Cancel")
  button(:change_post_save, css: "form.flow-edit-form .flow-edit-submit")
  button(:change_title_save, css: "form.flow-edit-title-form .flow-edit-submit")
  textarea(:comment_field, css: 'form.flow-topic-reply-form > textarea[name="topic_content"]')
  button(:comment_reply_save, css: "form.flow-topic-reply-form .flow-reply-submit")
  a(:contrib_link, text: "contribs")
  a(:edit_post, class: "flow-edit-post-link", index: topic_index)
  a(:edit_title_icon, css: "div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-edit-title > a.mw-ui-button.flow-edit-topic-link")
  div(:flow_body, class: "flow-container")
  div(:flow_topics, class: "flow-topics")
  div(:highlighted_comment, class: "flow-post-highlighted")

  # Collapse button
  a(:full_view, href: "#collapser/full")
  a(:small_view, href: "#collapser/compact")
  a(:collapsed_view, href: "#collapser/topics")

  # Buttons in a fly-out menu.
  button(:delete_button,         css: "div.tipsy-inner input.flow-delete-post-link")
  button(:hide_button,           css: "div.tipsy-inner input.flow-hide-post-link")
  button(:suppress_button,       css: "div.tipsy-inner input.flow-suppress-post-link")
  button(:topic_delete_button,   css: "div.tipsy-inner input.flow-delete-topic-link")
  button(:topic_hide_button,     css: "div.tipsy-inner input.flow-hide-topic-link")
  button(:topic_suppress_button, css: "div.tipsy-inner input.flow-suppress-topic-link")

  # New topic creation
  form(:new_topic_form, css: ".flow-newtopic-form")
  text_area(:new_topic_body, class: "flow-newtopic-content")
  button(:new_topic_cancel, css: ".flow-newtopic-form .flow-ui-destructive")
  button(:new_topic_preview, css: ".flow-newtopic-form .flow-ui-progressive")
  button(:new_topic_save, css: ".flow-newtopic-form .flow-ui-constructive")
  text_field(:new_topic_title, name: "topiclist_topic")
  a(:permalink, css: "div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-permalink > a.mw-ui-button.flow-action-permalink-link")

  # Replying
  form(:new_reply_form, css: ".flow-reply-form")
  button(:new_reply_cancel, css: ".flow-reply-form .flow-ui-destructive")
  button(:new_reply_preview, css: ".flow-reply-form .flow-ui-progressive")
  button(:new_reply_save, css: ".flow-reply-form .flow-ui-constructive")

  # Find Actions link within a particular class.
  # With jQuery the selector is simply $( '.flow-post-container
  # a:contains("Actions")' ) but in page-object, there are no good solutions.
  # There's https://github.com/cheezy/page-object/wiki/Nested-Elements but it
  # makes little sense.
  a(:post_actions_link, xpath: "//div[contains(concat(' ', normalize-space(@class), ' '), ' flow-post-container ')]//a[text()='Actions']", index: 1)
  text_area(:post_edit, css: "form.flow-edit-form .flow-edit-content")
  button(:preview_button, class: "mw-ui-button flow-preview-submit")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")
  a(:talk_link, text: "Talk")
  text_field(:title_edit, css: "form.flow-edit-title-form .flow-edit-content")
  a(:topic_actions_link, xpath: "//div[contains(concat(' ', normalize-space(@class), ' '), ' flow-topic-container ')]//a[text()='Actions']", index: actions_index)
  div(:topic_post, class: "flow-post-content", index: topic_index)
  div(:topic_title, class: "flow-topic-title", index: topic_index)

  div(:header_content, id: "flow-header-content", index: 0)
  a(:edit_header_link, title: "Edit header")
  form(:edit_header_form, class: "flow-edit-form") # Reuses common edit action so no header-specific class.
  text_field(:edit_header_textbox, class: "flow-edit-content") # Reuses common edit action so no header-specific class.
  button(:edit_header_save, text: "Save header")
end
