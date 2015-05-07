class WikiPage
  include PageObject
  a(:logout, css: "#pt-logout a")

  def scroll_to_top
    self.browser.execute_script("window.scrollTo(0, 0);")
  end
end
