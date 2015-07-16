@chrome @firefox @internet_explorer_10
@en.wikipedia.beta.wmflabs.org
Feature: Creating a new topic

  Background:
    Given I am on Flow page

  Scenario: Add new Flow topic as anonymous user
    When I have created a Flow topic with title "Anonymous user topic creation"
    Then the top post should have a heading which contains "Anonymous user topic creation"
    And the top post should have content which contains "Anonymous user topic creation"
