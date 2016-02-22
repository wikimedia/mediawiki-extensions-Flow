@chrome @firefox @internet_explorer_10
@vagrant
@en.wikipedia.beta.wmflabs.org
Feature: Summarize

  Background:
    Given there is a new topic
    And I am logged in
    And I am on Flow page

  Scenario: Summarize a topic
    When I summarize the first topic as "first summary version"
    Then the first topic is summarized as "first summary version"
    When I re-summarize the first topic as "second summary version"
    Then the first topic is summarized as "second summary version"
