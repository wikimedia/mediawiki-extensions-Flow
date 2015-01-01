@clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Flow updates are in Recent Changes

  Background:
    Given I am logged in
    And I am on Flow page
    And I have created a Flow topic with title "New topic should be in Recent Changes"

  Scenario: New topic is in Recent Changes
    Then the new topic should be in the Recent Changes page

  Scenario: Edited topic is in Recent Changes
    When I click the Edit title action
      And I edit the title field with Title should be in Recent Changes
      And I save the new title
      And I navigate to the Recent Changes page
    Then the new title should be in the Recent Changes page
