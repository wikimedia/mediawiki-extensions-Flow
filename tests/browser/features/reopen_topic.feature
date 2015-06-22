@chrome @internet_explorer_10 @firefox @login @clean @en.wikipedia.beta.wmflabs.org
Feature: Reopen a resolved topic

  Background:
    Given I am logged in
    And I am on Flow page
    And I have created a Flow topic

  Scenario: Reopening a resolved topic and changing the summary
    Given I mark the first topic as resolved
    And I summarize as "answer when resolving"
    When I reopen the first topic
    And I summarize as "answer when reopening"
    Then the first topic is open
    And the first topic is summarized as "answer when reopening"