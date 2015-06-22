@chrome @firefox @internet_explorer_10 @phantomjs
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Watching/Unwatching Boards and Topics

	Scenario: Watch topic
		Given I am logged in
			And I am on Flow page
			And I have created a Flow topic
			And I am not watching the Flow topic
		When I click the Watch Topic link
		Then I should see the Unwatch Topic link

	Scenario: Unwatch topic
		Given I am logged in
			And I am on Flow page
			And I have created a Flow topic
			And I am watching the Flow topic
		When I click the Unwatch Topic link
		Then I should see the Watch Topic link

	Scenario: Watch board
		Given I am logged in
			And I am on Flow page
			And I am not watching the Flow board
		When I click the Watch Board link
		Then I should see the Unwatch Board link

	Scenario: Unwatch board
		Given I am logged in
			And I am on Flow page
			And I am watching the Flow board
		When I click the Unwatch Board link
		Then I should see the Watch Board link

	Scenario: No watch links for anonymous users
		When I am on Flow page
		Then I should not see any watch links
