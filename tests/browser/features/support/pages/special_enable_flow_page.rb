class EnableFlowPage
  include PageObject

  include URL
  page_url URL.url('Special:EnableFlow')

  # form elements
  div(:page_name_div, id: 'mw-input-wppage')
  div(:submit_button_div, class: 'oo-ui-buttonElement')

  def page_name
    page_name_div_element.text_field_element
  end

  def submit_button
    submit_button_div_element.button_element
  end
end
