require "page-object"

class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  # This hack makes Chrome edit the second topic on the page to avoid edit
  # conflicts from simultaneous test runs (bug 59011).
  if ENV['BROWSER_LABEL'] == "chrome"
    topic_index = 1
  else
    topic_index = 0
  end

  a(:actions_link, text: "Actions", index: 1)
  span(:author_link, class: "flow-creator")
  a(:block_user, title: /Special:Block/)
  list_item(:collapsed_view, title: "Collapsed view")
  button(:change_post_save, class: "flow-edit-post-submit")
  button(:change_title_save, class: "flow-edit-title-submit")
  a(:contrib_link, text: "contribs")
  a(:edit_post_icon, title: "Edit post", index: topic_index)
  a(:edit_title_icon, title: "Edit title", index: topic_index)
  div(:flow_body, class: "flow-container")
  list_item(:full_view, title: "Full view")

  # Buttons in a fly-out menu.
  button(:delete_button,         css: "div.tipsy-inner input.flow-delete-post-link")
  button(:hide_button,           css: "div.tipsy-inner input.flow-hide-post-link")
  button(:suppress_button,       css: "div.tipsy-inner input.flow-suppress-post-link")
  button(:topic_delete_button,   css: "div.tipsy-inner input.flow-delete-topic-link")
  button(:topic_hide_button,     css: "div.tipsy-inner input.flow-hide-topic-link")
  button(:topic_suppress_button, css: "div.tipsy-inner input.flow-suppress-topic-link")

  text_area(:new_topic_body, class: "flow-newtopic-content")
  button(:new_topic_save, class: "flow-newtopic-submit")
  text_field(:new_topic_title, name: "topic_list[topic]")
  text_field(:post_edit, class: "flow-edit-post-content flow-disabler")
  div(:small_spinner, class: "mw-spinner mw-spinner-small mw-spinner-inline")
  list_item(:small_view, title: "Small view")
  a(:talk_link, text: "Talk")
  text_field(:title_edit, class: "mw-ui-input flow-edit-title-textbox")
  a(:topic_actions_link, text: "Actions", index: 0)
  div(:topic_post, class: "flow-post-content", index: topic_index)
  div(:topic_title, class: "flow-topic-title", index: topic_index)

  div(:header_content, id: "flow-header-content", index: 0)
  a(:edit_header_link, title: "Edit header")
  form(:edit_header_form, class: "flow-edit-header-form")
  text_field(:edit_header_textbox, class: "flow-edit-header-content")
  button(:edit_header_save, class: "flow-edit-header-submit")
end
