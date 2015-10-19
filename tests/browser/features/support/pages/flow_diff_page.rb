class FlowDiffPage
  include PageObject
  include FlowEditor

  div(:editor_widget, class: 'flow-ui-editorWidget')

  def editor_element
    visualeditor_or_textarea editor_widget_element
  end

  button(:undo_post_save) do
    editor_widget_element.link_element(css: '.flow-ui-editorControlsWidget-saveButton a')
  end
end
