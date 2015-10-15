@chrome @firefox @internet_explorer_10
@en.wikipedia.beta.wmflabs.org
Feature: Board description

  Assumes Flow is enabled for the Flow_test_talk namespace.

  Background:
    Given I am on a new board

  Scenario: No description on a new board
    Then the description should be empty

  Scenario: Create a description on a new board
    When I set the description to "first version"
    Then the description should be "first version"

  @integration
  Scenario: Edit the description on an existing board
    Given I set the description to "first version"
    When I set the description to "second version"
    Then the description should be "second version"

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
