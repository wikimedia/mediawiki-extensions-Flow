@chrome @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @vagrant
Feature: Check the interface for anonymous users

  Scenario: Anon does not see block or actions
  	Given there is a new topic
    When I am on Flow page
    Then I see a flow creator element
    And the block author link should not be visible
