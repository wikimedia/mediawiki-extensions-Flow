@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Create new topic anonymous

  Scenario: Anon does not see block or actions
    Given I am on Flow page
      And I have created a Flow topic
      # which is not hidden (this is implicit from the above step)
    When I see a flow creator element
    Then the block author link does not exist
