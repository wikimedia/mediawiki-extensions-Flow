require 'page-object'

class FlowPage
  include PageObject

  include URL
  page_url URL.url('Talk:Flow_QA')

  span(:author_link, class: 'flow-creator-simple')
  a(:cancel_button, class: 'flow-cancel-link mw-ui-button mw-ui-text')
  button(:change_title, value: 'Change title')
  a(:contrib_link, text: 'contribs')
  div(:flow_body, class: 'flow-container')
  text_area(:new_topic_body, class: 'flow-newtopic-content')
  div(:new_topic_body_ve, class: 've-ce-documentNode ve-ce-branchNode')
  button(:new_topic_save, class: 'flow-newtopic-submit')
  text_field(:new_topic_title, name: 'topic_list[topic]')
  a(:pencil_icon, text: 'Edit title')
  a(:talk_link, text: 'Talk')
  text_field(:title_textfield, class: 'mw-ui-input flow-edit-title-textbox')
  unordered_list(:topic_post, class: 'flow-topic-posts-meta')
end
