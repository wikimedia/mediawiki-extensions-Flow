@chrome @clean @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @test2.wikipedia.org
Feature: Actions menu Permalink

  Scenario: Actions menu Permalink
    Given I am on Flow page
      And page has no ResourceLoader errors
      And I have created a Flow topic
    When I click Actions menu for the Topic
      And I click Permalink from the Actions menu
      And I add 3 comments to the Topic
      And I click Actions menu for the 3rd comment on the Topic
      And I click Permalink from the Actions menu
    Then the highlighted comment should contain the text for the 3rd comment

