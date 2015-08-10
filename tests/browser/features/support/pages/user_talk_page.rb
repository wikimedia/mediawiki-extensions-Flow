require_relative 'wiki_page'

class UserTalkPage < WikiPage
  include PageObject

  page_url "./User_talk:<%= params[:username]%>"
end
