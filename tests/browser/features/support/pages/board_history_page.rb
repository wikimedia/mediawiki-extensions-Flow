class BoardHistoryPage
  include PageObject

  page_url "Talk:Flow_QA?action=history"

  div(:flow_board_history, class: 'flow-board-history')
end
