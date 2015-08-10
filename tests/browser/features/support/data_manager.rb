class DataManager
  def initialize
    @data = {}
  end

  def get(part)
    @data[part] = "#{part}_#{Random.srand}" unless @data.key? part
    @data[part]
  end

  def get_talk(part)
    get "Talk:#{part}"
  end
end
