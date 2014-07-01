@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Create new topic anonymous

  @wip
  Scenario: Topic button disabled by default
    Given I am on Flow page
    When I create a Title of Flow Topic in Flow new topic
    Then the add topic button is disabled

  @wip
  Scenario: Add new Flow topic
    Given I am on Flow page
    When I create a Title of Flow Topic in Flow new topic
      And I create a Body of Flow Topic into Flow body
      And the add topic button is enabled
      And I click New topic save
    Then the top post should have a heading which contains "Title of Flow Topic"
      And the top post should have content which contains "Body of Flow Topic"

  Scenario: Anon does not see block or actions
    Given I am on Flow page
    When I see a flow creator element
    Then the block author link does not exist
