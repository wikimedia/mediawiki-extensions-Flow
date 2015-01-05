@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @phantomjs @test2.wikipedia.org
Feature: Check the interface for anonymous users

  Scenario: Anon does not see block or actions
    Given I am on Flow page
      And I have created a Flow topic with title "Checking for block author"
    When I see a flow creator element
    Then the block author link should not be visible
