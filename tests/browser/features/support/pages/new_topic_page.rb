class NewTopicPage < FlowPage
  ## First post with link HTML in topic title
  a(:flow_first_topic_main_page_link) do
    h2_element(css: ".flow-topic-title", index: 0).link_element(href: %r{/wiki/Main_Page})
  end

  a(:flow_first_topic_red_link) do
    h2_element(css: ".flow-topic-title", index: 0).link_element(class: 'new')
  end

  a(:flow_first_topic_media_link) do
    h2_element(css: ".flow-topic-title", index: 0).link_element(href: 'https://upload.wikimedia.org/wikipedia/commons/3/36/Earth.jpg')
  end
end
