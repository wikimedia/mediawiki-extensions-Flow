@chrome @firefox @internet_explorer_10
@vagrant
@en.wikipedia.beta.wmflabs.org
Feature: Undoing edits

  Background:
    Given I am logged in

  Scenario: Undo an edit to a post
    Given I am on a new Flow board with topic content "This is the original content"
    And I edit the topic with "This is the edited content"
    When I visit the topic history page
    And I undo the latest action
    Then I am on a Flow topic page
    And the saved undo post should contain "This is the original content"

  Scenario: Undo an edit to the description
    Given I am on a new Flow board with description "This is the original content"
    And I set the description to "This is the edited content"
    And I visit the new board history page
    When I undo the latest action
    Then I am on a Flow page
    And the description should be "This is the original content"
