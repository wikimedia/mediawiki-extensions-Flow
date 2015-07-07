module Watir
  class Div
    def clear
      send_keys [:command, 'a'], :backspace
      send_keys [:control, 'a'], :backspace
    end
  end
end
