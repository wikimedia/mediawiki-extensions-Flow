class WikiPage
  include PageObject

  page_url "<%=params[:page]%>"

  a(:logout, css: "#pt-logout a")
  div(:content, id: 'mw-content-text')
  page_section(:flow, FlowComponent, class: 'flow-component')
end
