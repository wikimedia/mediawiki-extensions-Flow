require "watir-webdriver/wait"

module Watir
  class Div
    def clear
      send_keys [:command, 'a'], :backspace
      send_keys [:control, 'a'], :backspace
    end
  end

  class TextArea
    def when_enabled
      Watir::Wait.until { !self.disabled? }
      self
    end

    def text
      value
    end
  end
end

module PageObject
  def refresh_until(timeout = PageObject.default_page_wait, message = nil, &block)
    platform.wait_until(timeout, message) do
      yield.tap do |result|
        refresh unless result
      end
    end
  end
end
