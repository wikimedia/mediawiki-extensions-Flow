Feature: Creating a new topic

  Background:
    Given I am on Flow page

  Scenario: Cannot create a new topic without content
    When I type "Anonymous user topic creation test" into the new topic title field
    Then the Save New Topic button should be disabled

  Scenario: Add new Flow topic as anonymous user
    When I type "Anonymous user topic creation test" into the new topic title field
      And I type "Anon test." into the new topic content field
      And I click New topic save
    Then the top post should have a heading which contains "Anonymous user topic creation test"
      And the top post should have content which contains "Anon test."
