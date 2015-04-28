class DataManager
  def initialize
    @data = {}
  end

  def get (part)
    unless @data.key? part
      @data[part] = "#{part}-#{rand}"
    end
    @data[part]
  end
end