@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Follow user links

  Scenario: User links takes me to the user page
    Given I am logged in
      And I am on Flow page
      And I have created a Flow topic
      And I see a flow creator element
    When I click the flow creator element
    Then I am on my user page

    
