@chrome @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @phantomjs @test2.wikipedia.org
Feature: Sorting topics

  Background:
    Given I am on Flow page

  Scenario: Switch topic sorting to Recently Active Topics
    When I click Newest topics
      And I click Recently active topics choice
    Then the Flow page shows Recently active topics
      And the Flow page does not show Newest topics

  Scenario: Switch topic sorting to Recently Active Topics and then back to Newest topics
    When I click Newest topics
      And I click Recently active topics choice
      And I click Recently active topics in the page
      And I click Newest topics choice
    Then the Flow page shows Newest topics
      And the Flow page does not show Recently active topics
