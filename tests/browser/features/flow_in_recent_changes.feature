@clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Flow updates are in Recent Changes

  Background:
    Given I am logged in
    And I am on Flow page
    And I have created a Flow topic

  Scenario: New topic is in Recent Changes
    When I navigate to the Recent Changes page
    Then the new topic is in the Recent Changes page

  Scenario: Edited topic is in Recent Changes
    When I click the Edit title action
      And I edit the title field with Title edited
      And I save the new title
      And I click Edit post
      And I edit the post field with Post edited
      And I save the new post
      And I navigate to the Recent Changes page
    Then the new title should be in the Recent Changes page
      And the new post content should be in the Recent Changes page