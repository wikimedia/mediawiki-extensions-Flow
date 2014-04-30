@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @test2.wikipedia.org
Feature: Create new topic anonymous

  Scenario: Add new Flow topic
    Given I am on Flow page
    When I create a Title of Flow Topic in Flow new topic
      And I create a Body of Flow Topic into Flow body
      And I click New topic save
    Then the Flow page should contain Title of Flow Topic
      And the Flow page should contain Body of Flow Topic

  Scenario: Anon does not see block or actions
    Given I am on Flow page
    When I see a flow creator element
    Then I do not see a block user link
