require "watir-webdriver/wait"

module Watir
  class Div
    def clear
      send_keys [:command, 'a'], :backspace
      send_keys [:control, 'a'], :backspace
    end
  end

  class TextArea
    def enabled?
      !disabled?
    end

    def text
      value
    end
  end
end

module PageObject
  def refresh_until(timeout = PageObject.default_page_wait, message = nil)
    platform.wait_until(timeout, message) do
      yield.tap do |result|
        refresh unless result
      end
    end
  end
end

module PageObject
  module Elements
    class TextArea
      def when_enabled
        wait_until { enabled? }
        self
      end
    end

    class TextField
      def when_enabled
        wait_until { enabled? }
        self
      end
    end
  end
end
