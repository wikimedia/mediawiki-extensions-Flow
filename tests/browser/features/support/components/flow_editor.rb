module FlowEditor
  def visualeditor_or_textarea(form)
    parent = form.is_a?(String) ? form_element(css: form) : form
    parent.when_present
    if parent.div_element(class: 'flow-ui-wikitextEditorWidget').exists?
      parent.div_element(class: 'flow-ui-wikitextEditorWidget').text_area_element.when_enabled
    else
      parent.div_element(class: 've-ce-documentNode')
    end
  end
end
