@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Create new topic anonymous

  Scenario: Add new Flow topic
    Given I am on Flow page
    When I type "Title of Flow Topic" into the new topic title field
      And I type "Body of Flow Topic" into the new topic content field
      And I click New topic save
    Then the top post should have a heading which contains "Title of Flow Topic"
      And the top post should have content which contains "Body of Flow Topic"

  Scenario: Anon does not see block or actions
    Given I am on Flow page
      And I have created a Flow topic
      # which is not hidden (this is implicit from the above step)
    When I see a flow creator element
    Then the block author link does not exist
