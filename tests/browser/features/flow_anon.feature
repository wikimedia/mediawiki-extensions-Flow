@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Create new topic anonymous

  Scenario: Add new Flow topic
    Given I am on Flow page
    When I create a Title of Flow Topic in Flow new topic
      And I create a Body of Flow Topic into Flow body
      And I click New topic save
    Then the top post should have a heading which contains "Title of Flow Topic"
      And the top post should have content which contains "Body of Flow Topic"

  Scenario: Anon does not see block or actions
    Given I am on Flow page
    When I see a flow creator element
    Then the block author link does not exist
