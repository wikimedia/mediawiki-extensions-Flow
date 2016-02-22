@chrome @firefox @internet_explorer_10
Feature: Categories

  Assumes Flow is enabled for the Flow_test_talk namespace.

  Background:
    Given I am on a new board

  Scenario: Add a category to new board
    When I add category "Footegory" to the description
    Then the categories contain "Footegory"

  Scenario: Add multiple categories to new board
    When I add categories "Footegory" and "Mootegory" to the description
    Then the categories contain "Footegory"
    And the categories contain "Mootegory"

  Scenario: Remove categories from a new board
    Given the board contains categories "Footegory" and "Mootegory"
    When I remove category "Footegory" from the description
    Then the categories do not contain "Footegory"
    And the categories contain "Mootegory"
