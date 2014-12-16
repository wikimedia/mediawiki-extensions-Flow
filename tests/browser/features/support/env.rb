require "mediawiki_selenium"

require "mediawiki_selenium/support/env"
require "mediawiki_selenium/support/hooks"
require "mediawiki_selenium/support/pages"
require "mediawiki_selenium/step_definitions"

require_relative "flow_helper"

World(FlowHelper)

if  ENV['PAGE_WAIT_TIMEOUT']
  PageObject.default_page_wait = ENV['PAGE_WAIT_TIMEOUT'].to_i
end

if  ENV['ELEMENT_WAIT_TIMEOUT']
  PageObject.default_element_wait = ENV['ELEMENT_WAIT_TIMEOUT'].to_i
end
