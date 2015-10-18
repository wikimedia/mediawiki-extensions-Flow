@chrome @firefox @internet_explorer_10
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Undoing edits

  Background:
    Given I am logged in
    And I am on Flow page

  Scenario: Undo an edit to a post
    Given there is a new topic with title "original title for undo"
    And I select Edit post
    And I edit the post field with "Post edited"
    And I save the new post
    When I visit the topic history page
    And I click undo
    And I am on a Flow diff page
    And I save the undo post
    Then I am on a Flow topic page
    And the saved undo post should contain "created via API"

  Scenario: Undo an edit to the description
    Given I set the description to "first version"
    And I set the description to "second version"
    And I visit the board history page
    And I click undo
    And I am on a Flow diff page
    When I save the undo post
    Then I am on Flow page
    And the description should be "first version"
