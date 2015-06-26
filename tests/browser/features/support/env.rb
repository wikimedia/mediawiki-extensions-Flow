require "mediawiki_api"
require "mediawiki_selenium"
require_relative 'div_extension'

if  ENV['PAGE_WAIT_TIMEOUT']
  PageObject.default_page_wait = ENV['PAGE_WAIT_TIMEOUT'].to_i
end

if  ENV['ELEMENT_WAIT_TIMEOUT']
  PageObject.default_element_wait = ENV['ELEMENT_WAIT_TIMEOUT'].to_i
end
