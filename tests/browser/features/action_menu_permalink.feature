@chrome @firefox @internet_explorer_10
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Actions menu Permalink

  Background:
    Given I am logged in
    And I am on Flow page

  Scenario: Topic Actions menu Permalink
    Given I have created a Flow topic with title "Permalinktest"
    When I hover on the Topic Actions link
    And I click Permalink from the Actions menu
    And I am viewing Topic page
    Then I see only one topic on the page
    And the top post should have a heading which contains "Permalinktest"

  Scenario: Actions menu Permalink
    Given I have created a Flow topic
    And I reply with comment "this is my response"
    When I select the permalink for the second post of the first topic
    Then I am viewing Topic page
    And I see only one topic on the page
    And the highlighted comment is "this is my response"

  Scenario: Old style topic permalink
    Given I have created a Flow topic with title "Permalinktest"
    When I go to an old style permalink to my topic
    And I am viewing Topic page
    Then I see only one topic on the page
    And the top post should have a heading which contains "Permalinktest"
