@chrome @firefox @internet_explorer_10
Feature: Reply moderation

  Background:
    Given I am logged in
    And I am on Flow page

  Scenario: Hiding a comment
    Given I have created a Flow topic with title "Hide comment test"
    And I reply with comment "hide me if you dare"
    When I hide the second comment with reason "Shhhhh!"
    Then the second comment should be marked as hidden
    And the content of the second comment should not be visible
