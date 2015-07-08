class SpecialConversionFlowArchivePage
  include PageObject

  def content_paragraph
    div_element(class:'flow-board-header-content').p.text
  end
end
