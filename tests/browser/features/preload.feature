@chrome @firefox @internet_explorer_10
@vagrant
@en.wikipedia.beta.wmflabs.org
Feature: preload

  Background:
    Given there is a page to preload content from

  Scenario: Preloading title and content
    When I am on Flow page with the title and content preload parameters
    Then the title is preloaded
    And the content is preloaded

  Scenario: Preloading title only
    When I am on Flow page with the title preload parameter
    Then the title is preloaded
    And the content is empty

  Scenario: Preloading content only
    When I am on Flow page with the content preload parameter
    Then the content is preloaded
    And the title is empty
