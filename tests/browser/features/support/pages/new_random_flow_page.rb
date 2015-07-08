require "page-object"

class NewRandomFlowPage < WikiPage
  div(:flow_page, class:'flow-component board-page')
  div(:flow_board, class:'flow-board')
  # board description
  a(:edit_description_link, title: "Edit description")
  div(:description_content, css: ".flow-board-header-content")
  form(:edit_description_form, css: ".edit-header-form")
end
