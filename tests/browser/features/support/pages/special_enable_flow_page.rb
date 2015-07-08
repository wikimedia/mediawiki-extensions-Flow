class EnableFlowPage
  include PageObject

  include URL
  page_url URL.url('Special:EnableFlow')

  # form elements
  text_field(:page_name, :id => 'mw-input-wppage')
  text_field(:archive_name, :id => 'mw-input-wparchive-title-format')
  text_area(:page_description, :id => 'mw-input-wpheader')

  def submit_button
  	parent = div_element(class:'mw-htmlform-submit-buttons')
  	parent.button_element.when_present
  end
end
