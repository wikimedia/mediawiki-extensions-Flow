class WikiPage
  include PageObject

  page_url "<%=params[:page]%>"

  a(:logout, css: "#pt-logout a")

  div(:content, id: 'mw-content-text')

  def scroll_to_top
    browser.execute_script("window.scrollTo(0, 0);")
  end
end
