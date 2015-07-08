class DataManager
  def initialize
    @data = {}
  end

  def get(part)
    @data[part] = "#{part}-#{rand}" unless @data.key? part
    @data[part]
  end

  def get_talk(part)
    @data[part] = "Talk:#{part}-#{rand}" unless @data.key? part
    @data[part]
  end
end
