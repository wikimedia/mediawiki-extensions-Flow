module FlowEditor
  def visualeditor_or_textarea(form)
    parent = form.is_a?(String) ? form_element(css: form) : form
    parent.when_present
    parent.div_element(class: 've-ce-documentNode')
  end
end
