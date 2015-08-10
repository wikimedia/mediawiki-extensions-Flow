class DataManager
  def initialize
    @data = {}
  end

  def get(part)
    @data[part] = "#{part}_#{rand(999_999)}" unless @data.key? part
    @data[part]
  end

  def get_talk(part)
    get "Talk:#{part}"
  end
end
