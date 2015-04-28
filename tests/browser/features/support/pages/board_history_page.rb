class BoardHistoryPage
  include PageObject

  include URL

  page_url URL.url("Talk:Flow_QA?action=history")

  div(:flow_board_history, class: 'flow-board-history')
end
