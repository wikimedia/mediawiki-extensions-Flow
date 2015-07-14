class EnableFlowConfirmationPage
  include PageObject

  div(:content_div, id: 'mw-content-text')
  def new_board_link
    content_div_element.link_element
  end
end
