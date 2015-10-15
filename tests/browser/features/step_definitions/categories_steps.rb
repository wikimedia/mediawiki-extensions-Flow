When(/^I add category "(.*?)" to the description$/) do |category_text|
  on(FlowPage) do |page|
    page.description.edit
    page.description.editor_element.when_present.send_keys '[[Category:' + category_text + ']]'
    page.description.save
    page.description.categories_element.when_present
  end
end

When(/^I add categories "(.*?)" and "(.*?)" to the description$/) do |cat1, cat2|
  on(FlowPage) do |page|
    page.description.edit
    page.description.editor_element.when_present.send_keys '[[Category:' +
      cat1 + ']]' + "\n" \
      '[[Category:' + cat2 + ']]'
    page.description.save
    page.description.categories_element.when_present
  end
end

When(/^I remove category "(.*?)" from the description$/) do |category_text|
  on(FlowPage) do |page|
    page.description.edit
    text = page.description.editor_element.when_present.text
    text.slice! "[[Category:#{category_text}]]"
    page.description.editor_element.when_present.clear
    page.description.editor_element.when_present.send_keys text
    page.description.save
    page.description.categories_element.when_present
  end
end

When(/^the board contains categories "(.*?)" and "(.*?)"$/) do |cat1, cat2|
  step "I add categories \"#{cat1}\" and \"#{cat2}\" to the description"
end

When(/^the categories contain "(.*?)"$/) do |category_text|
  on(FlowPage) do |page|
    page.description.category_item(category_text).exists?
  end
end

When(/^the categories do not contain "(.*?)"$/) do |category_text|
  on(FlowPage) do |page|
    expect(page.description.category_item(category_text)).not_to exist
  end
end
