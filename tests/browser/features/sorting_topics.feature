@chrome @firefox @internet_explorer_10 @phantomjs
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Sorting topics

  Background:
    Given I am on Flow page

  Scenario: Switch topic sorting to Recently Active Topics
    When I click Newest topics link
      And I click Recently active topics choice
    Then the Flow page should show Recently active topics link
      And the Flow page should not show Newest topics link

  Scenario: Switch topic sorting to Recently Active Topics and then back to Newest topics
    When I click Newest topics link
      And I click Recently active topics choice
      And I click Recently active topics link
      And I click Newest topics choice
    Then the Flow page should show Newest topics link
      And the Flow page should not show Recently active topics link
