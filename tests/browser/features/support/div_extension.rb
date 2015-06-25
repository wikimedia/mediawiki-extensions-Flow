module Watir
  class Div
    def clear
      send_keys [:control, 'a'], :backspace
      send_keys [:command, 'a'], :backspace
    end
  end
end
