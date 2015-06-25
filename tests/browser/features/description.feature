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

  Scenario: Edit description on a new board
    Given I am on a new board
    When I set the description to "test12345"
    Then the description should be "test12345"
