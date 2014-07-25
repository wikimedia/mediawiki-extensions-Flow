Given(/^I am (not )?watching the Flow (board|topic)$/) do |not_watching, type|
	on(FlowPage) do |page|
		if ( type === 'board' ) then
			container = page.board_watchlist_container
		elsif ( type === 'topic' ) then
			container = page.first_topic_watchlist_container
		end

		watch_link = container.link_element( :class => 'flow-watch-link-watch' )
		unwatch_link = container.link_element( :class => 'flow-watch-link-unwatch' )

		if ( ! not_watching ) then ## Make sure we *are* watching something
			element = watch_link
			other_element = unwatch_link
		else ## Make sure we *are not* watching something
			element = unwatch_link
			other_element = watch_link
		end

		if element.visible? then
			element.click
			other_element.when_present
		end
	end
end

When(/^I click the (Watch|Unwatch) (Topic|Board) link$/) do |action, target|
	if ( type === 'Board' ) then
		container = page.board_watchlist_container
	elsif ( type === 'Topic' ) then
		container = page.first_topic_watchlist_container
	end

	watch_link = container.link_element( :class => 'flow-watch-link-watch' )
	unwatch_link = container.link_element( :class => 'flow-watch-link-unwatch' )

	if ( action === 'Unwatch' ) then
		unwatch_link.when_present.click
	elsif ( action === 'Watch' ) then
		watch_link.when_present.click
	end
end

Then(/^I should see a (Watch|Unwatch) (Topic|Board)$/) do |action, target|
	if ( type === 'Board' ) then
		container = page.board_watchlist_container
	elsif ( type === 'Topic' ) then
		container = page.first_topic_watchlist_container
	end

	watch_link = container.link_element( :class => 'flow-watch-link-watch' )
	unwatch_link = container.link_element( :class => 'flow-watch-link-unwatch' )

	if ( action === 'Unwatch' ) then
		unwatch_link.should be_visible
	elsif ( action === 'Watch' ) then
		watch_link.should be_visible
	end
end
