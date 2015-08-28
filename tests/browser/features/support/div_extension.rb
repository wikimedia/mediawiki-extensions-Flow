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
  end
end
