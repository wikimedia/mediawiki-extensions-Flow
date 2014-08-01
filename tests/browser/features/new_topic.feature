Feature: Creating a new topic

  Background:
    Given I am on Flow page

  Scenario: Cannot create a new topic without content
    When I create a Title in Flow new topic
    Then the Save New Topic button should be disabled

  Scenario: Add new Flow topic as anonymous user
    When I create a Title of Flow Topic in Flow new topic
      And I create a Body of Flow Topic into Flow body
      And I click New topic save
    Then the top post should have a heading which contains "Title of Flow Topic"
      And the top post should have content which contains "Body of Flow Topic"
