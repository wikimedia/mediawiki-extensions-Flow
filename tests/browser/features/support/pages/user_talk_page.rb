class UserTalkPage < AbstractFlowPage
  include PageObject

  page_url "./User_talk:<%= params[:username]%>"
end