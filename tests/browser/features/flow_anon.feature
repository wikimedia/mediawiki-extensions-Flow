@en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Create new topic anonymous

  Scenario: Add new Flow topic
    Given I am on Flow page
    When I create a Title of Flow Topic in Flow new topic
      And I create a Body of Flow Topic into Flow body
      And I click New topic save
    Then the Flow page should contain Title of Flow Topic
      And the Flow page should contain Body of Flow Topic
