@en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10.0 @vagrant
Feature: Watching/Unwatching Boards and Topics

  Background:
    Given there is a new topic

  Scenario: Watch topic
    Given I am logged in
    And I am on Flow page
    And I am not watching the Flow topic
    When I click the Watch Topic link
    Then I should see the Unwatch Topic link

  @chrome
  Scenario: Unwatch topic
    Given I am logged in
    And I am on Flow page
    And I am watching the Flow topic
    When I click the Unwatch Topic link
    Then I should see the Watch Topic link

  @chrome
  Scenario: Watch board
    Given I am logged in
    And I am on Flow page
    And I am not watching the Flow board
    When I click the Watch Board link
    Then I should see the Unwatch Board link

  @chrome
  Scenario: Unwatch board
    Given I am logged in
    And I am on Flow page
    And I am watching the Flow board
    When I click the Unwatch Board link
    Then I should see the Watch Board link

  @chrome
  Scenario: No watch links for anonymous users
    When I am on Flow page
    Then I should not see any watch links
