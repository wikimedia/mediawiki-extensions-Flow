@chrome @firefox @internet_explorer_10
@vagrant
Feature: Moderation

  Background:
    Given there is a new topic
    And I am logged in
    And I am on Flow page

  Scenario: Deleting a topic
    When I select the Delete topic button
    And I see a dialog box
    And I give reason for deletion as being "He's a naughty boy"
    And I click Delete topic
    Then the top post should be marked as deleted
