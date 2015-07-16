Given(/^there is a new topic with title \"(.*?)\"$/) do |title|
  title = @data_manager.get title
  api.action('flow', submodule: 'new-topic', page: 'Talk:Flow_QA', nttopic: title, ntcontent: 'created via API')
end

Given(/^there is a new topic$/) do
  step "there is a new topic with title \"title\""
end

Given(/^there is a new topic created by me$/) do
  api.log_in user, password unless api.logged_in?
  step "there is a new topic with title \"title\""
end
