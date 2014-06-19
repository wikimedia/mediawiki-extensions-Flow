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
  list_item(:collapsed_view, title: "Collapsed view")
  button(:change_post_save, css: "form.flow-edit-form .flow-edit-submit")
  # frontend-rewrite: no more unique class for each button like .flow-reply-submit
  button(:change_title_save, css: "form.flow-edit-title-form .flow-ui-constructive")
  # frontend-rewrite: all reply forms have same class
  textarea(:comment_field, css: 'form.flow-reply-form > textarea[name="topic_content"]')
  # frontend-rewrite: no more unique class for each button like .flow-reply-submit
  # There are lots of Reply buttons matching this, hope the first one is the one we want.
  button(:comment_reply_save, css: "form.flow-reply-form .flow-ui-constructive")
  a(:contrib_link, text: "contribs")
  # frontend-rewrite: no more unique class like .flow-edit-post-link'
  a(:edit_post, css: ".flow-post-meta-actions a.flow-ui-regressive", index: topic_index)
  # frontend-rewrite: Action menu items no longer have unique classes like .flow-action-edit-title
  a(:edit_title, text: "Edit title" )
  # A better selector for the Flow container would be data-flow-component="board"
  div(:flow_body, class: "flow-component")
  div(:flow_topics, class: "flow-topics")
  list_item(:full_view, title: "Full view")
  div(:highlighted_comment, class: "flow-post-highlighted")

  # Buttons in a fly-out menu.
  button(:delete_button,         css: "div.tipsy-inner input.flow-delete-post-link")
  button(:hide_button,           css: "div.tipsy-inner input.flow-hide-post-link")
  button(:suppress_button,       css: "div.tipsy-inner input.flow-suppress-post-link")
  button(:topic_delete_button,   css: "div.tipsy-inner input.flow-delete-topic-link")
  button(:topic_hide_button,     css: "div.tipsy-inner input.flow-hide-topic-link")
  button(:topic_suppress_button, css: "div.tipsy-inner input.flow-suppress-topic-link")

  text_area(:new_topic_body, name: "topiclist_content")
  # frontend-rewrite: No more class="flow-newtopic-submit" on flow_newtopic_form.handlebars
  button(:new_topic_save, text: "Add topic")
  text_field(:new_topic_title, name: "topiclist_topic")
  # frontend-rewrite: Action menu items no longer have unique classes like .flow-action-permalink
  # 'css cannot be combined with other selectors ({:text=>"Permalink"}) (ArgumentError)', so can't use
  #     a(:permalink, css: ".flow-topic-titlebar .flow-menu li > a", text: "Permalink")
  a(:permalink, text: "Permalink")

  a(:post_actions_link, class: "flow-post-actions")
  text_area(:post_edit, css: 'form textarea[name="topic_content"]')
  button(:preview_button, class: "mw-ui-button flow-preview-submit")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")
  list_item(:small_view, title: "Small view")
  a(:talk_link, text: "Talk")
  text_field(:title_edit, css: 'form.flow-edit-title-form input[name="topic_content"]')
  a(:topic_actions_link, class: "flow-topic-actions")
  div(:topic_post, class: "flow-post-content", index: topic_index)
  h2(:topic_title, class: "flow-topic-title", index: topic_index)

  div(:header_content, id: "flow-header-content", index: 0)
  a(:edit_header_link, title: "Edit header")
  form(:edit_header_form, class: "flow-edit-form") # Reuses common edit action so no header-specific class.
  text_field(:edit_header_textbox, class: "flow-edit-content") # Reuses common edit action so no header-specific class.
  button(:edit_header_save, text: "Save header")
end
