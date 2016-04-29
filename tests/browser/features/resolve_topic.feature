@chrome @internet_explorer_10
@vagrant
Feature: Mark topic as resolved

  Background:
    Given there is a new topic
    And I am logged in
    And I am on Flow page

  @en.wikipedia.beta.wmflabs.org @firefox
  Scenario: Resolving a topic without a summary
    When I mark the first topic as resolved
    And I skip the summary
    Then the first topic is resolved

  @en.wikipedia.beta.wmflabs.org @firefox
  Scenario: Resolving a topic and adding a summary
    When I mark the first topic as resolved
    And I summarize as "the answer is 42"
    Then the first topic is resolved with summary "the answer is 42"

  @firefox
  Scenario: Resolving a topic and keeping the summary
    Given I summarize the first topic as "this answer should be kept"
    When I mark the first topic as resolved
    And I keep the summary
    Then the first topic is resolved with summary "this answer should be kept"

  @en.wikipedia.beta.wmflabs.org @integration
  Scenario: Resolving a topic and updating the summary
    Given I summarize the first topic as "this answer should be changed"
    When I mark the first topic as resolved
    And I summarize as "this is the new answer"
    Then the first topic is resolved with summary "this is the new answer"
