@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org
Feature: Watching/Unwatching Boards and Topics

	Scenario: Watch topic
		Given I am logged in
			And I am on a Flow board
			And I have created a Flow topic
			And I am not watching the Flow topic
		When I click the Watch Topic link
		Then I should see the Unwatch Topic link

	Scenario: Unwatch topic
		Given I am logged in
			And I am on a Flow board
			And I have created a Flow topic
			And I am watching the Flow topic
		When I click the Unwatch Topic link
		Then I should see the Watch Topic link

	Scenario: Watch board
		Given I am logged in
			And I am on a Flow board
			And I am not watching the Flow board
		When I click the Watch Board link
		Then I should see the Unwatch Board link

	Scenario: Unwatch board
		Given I am logged in
			And I am on a Flow board
			And I am watching the Flow board
		When I click the Unwatch Board link
		Then I should see the Watch Board link

	Scenario: No watch links for anonymous users
		When I am on a Flow board
		Then I should not see any watch links