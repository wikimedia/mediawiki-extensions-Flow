@chrome @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @phantomjs @test2.wikipedia.org
Feature: Sorting topics

  Background:
    Given I am on Flow page

  Scenario: Switch topic sorting to Recently Active Topics
    When I click Newest topics
      And I click Recently active topics choice
    Then the Flow page should show Recently active topics
      And the Flow page should not show Newest topics

  Scenario: Switch topic sorting to Recently Active Topics and then back to Newest topics
    When I click Newest topics
      And I click Recently active topics choice
      And I click Recently active topics in the page
      And I click Newest topics choice
    Then the Flow page should show Newest topics
      And the Flow page should not show Recently active topics
