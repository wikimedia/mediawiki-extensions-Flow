@chrome @firefox
@vagrant
Feature: Create new topic logged in

  It requires the cldr extension, a "Flow QA" page, and a "Selenium user" who has
  permission to flow-delete (usually 'sysop'/administrator user right), to
  flow-suppress (usually the 'oversight' user right), and to block (usually 'sysop').

  Background:
    Given there is a new topic created by me
    And I am logged in
    And I am on Flow page

  Scenario: Add new Flow topic and show author and block links
    Given the author link is visible
    And the talk to author link is not visible
    And the block author link is not visible
    When I hover over the author link
    Then the talk to author link should be visible
    And the block author link should be visible
