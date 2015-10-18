class BoardHistoryPage
  include PageObject

  page_url "./<%=params[:pagetitle] ? params[:pagetitle] + '?action=history' : 'Talk:Flow_QA?action=history' %>"

  div(:flow_board_history, class: 'flow-board-history')

  ul(:flow_board_history_moderation, class: 'flow-history-moderation-menu')

  link(:undo_link) do
    flow_board_history_moderation_element.link_element(text: /undo/)
  end
end
