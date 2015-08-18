class EnableFlowPage
  include PageObject

  page_url 'Special:EnableFlow'

  # form elements
  div(:page_name_div, id: 'mw-input-wppage')
  div(:page_header_div, id: 'mw-input-wpheader')
  div(:submit_button_div, class: 'oo-ui-buttonElement')

  text_field(:page_name) do
    page_name_div_element.text_field_element
  end

  text_area(:page_header) do
    page_header_div_element.text_area_element
  end

  button(:submit) do
    submit_button_div_element.button_element
  end
end
