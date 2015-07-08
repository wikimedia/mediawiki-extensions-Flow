class EnableFlowPage
  include PageObject

  include URL
  page_url URL.url('Special:EnableFlow')

  # form elements
  text_field(:page_name, id: 'mw-input-wppage')
  text_field(:archive_name, id: 'mw-input-wparchive-title-format')
  text_area(:page_description, id: 'mw-input-wpheader')
  div(:submit_buttons_div, class: 'mw-htmlform-submit-buttons')
  def submit_button
    submit_buttons_div_element.button_element
  end
end
