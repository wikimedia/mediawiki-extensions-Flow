class EnableFlowConfirmationPage
  include PageObject

  def new_board_link
    parent = div_element(id:'mw-content-text')
    parent.link_element().when_present
  end
end
