require "page-object"

class FlowPage
  include PageObject

  include URL
  # MEDIAWIKI_URL must have this in $wgFlowOccupyPages array or $wgFlowOccupyNamespaces.
  page_url URL.url("Talk:Flow_QA")

  a(:actions_link, text: 'Actions', index: 1)
  span(:author_link, class: 'flow-creator')
  a(:block_user, title: /Special:Block/)
  button(:change_post_save, class: 'mw-ui-button mw-ui-constructive flow-edit-post-submit')
  button(:change_title_save, class: 'flow-edit-title-submit mw-ui-button mw-ui-constructive')
  a(:contrib_link, text: 'contribs')
  button(:delete_button, css: 'div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-delete > form > input.flow-delete-post-link.mw-ui-button')
  a(:edit_post_icon, title: 'Edit post')
  a(:edit_title_icon, title: 'Edit title')
  div(:flow_body, class: 'flow-container')
  button(:hide_button, css: 'div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-hide > form > input.flow-hide-post-link.mw-ui-button')
  text_area(:new_topic_body, class: 'flow-newtopic-content')
  button(:new_topic_save, class: 'flow-newtopic-submit')
  text_field(:new_topic_title, name: 'topic_list[topic]')
  text_field(:post_edit, class: 'flow-edit-post-content flow-disabler')
  div(:small_spinner, class: 'mw-spinner mw-spinner-small mw-spinner-inline')
  button(:suppress_button, css: 'div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-suppress > form > input.flow-suppress-post-link.mw-ui-button')
  a(:talk_link, text: 'Talk')
  text_field(:title_edit, class: 'mw-ui-input flow-edit-title-textbox')
  a(:topic_actions_link, text: 'Actions', index: 0)
  button(:topic_delete_button, css: 'div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-delete > form > input.mw-ui-button.flow-delete-topic-link')
  button(:topic_hide_button, css: 'div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-hide > form > input.mw-ui-button.flow-hide-topic-link')
  div(:topic_post, class: 'flow-post-content')
  button(:topic_suppress_button, css: 'div.tipsy-inner > div.flow-tipsy-flyout > ul > li.flow-action-suppress > form > input.mw-ui-button.flow-suppress-topic-link')
  div(:topic_title, class: 'flow-topic-title')
end
