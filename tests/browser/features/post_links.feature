@chrome @en.wikipedia.beta.wmflabs.org @firefox @skip
Feature: Follow user links

  Background:
    Given there is a new topic created by me

  Scenario: User links takes me to the user page
    Given I am on Flow page
    When I click the flow creator element
    Then I am on my user page
