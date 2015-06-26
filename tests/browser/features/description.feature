@chrome @firefox @internet_explorer_10
@login
@en.wikipedia.beta.wmflabs.org
Feature: Board description

  Assumes Flow is enabled for the Flow_test_talk namespace.

  Background:
    Given I am logged in

  Scenario: No description on a new board
    When I am on a new board
    Then the description should be empty

  Scenario: Create a description on a new board
    Given I am on a new board
    When I set the description to "first version"
    Then the description should be "first version"

  Scenario: Edit the description on an existing board
    Given I am on a new board
    And I set the description to "first version"
    When I set the description to "second version"
    Then the description should be "second version"
