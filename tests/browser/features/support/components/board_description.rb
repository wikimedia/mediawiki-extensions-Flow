require_relative 'flow_editor'

class BoardDescription
  include PageObject
  include FlowEditor

  # board description
  a(:edit, text: "Edit description")
  div(:content, class: 'flow-ui-boardDescriptionWidget-content')

  a(:toggle, class: "side-rail-toggle-button")

  div(:editor_widget, class: 'flow-ui-boardDescriptionWidget-editor')

  def editor_element
    visualeditor_or_textarea editor_widget_element
  end

  link(:save, text: "Save description")

  # If page has an archive template from a flow conversion
  # find the link
  link(:archive_link) do
    content_element.link_element
  end
end
