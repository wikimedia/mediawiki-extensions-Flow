@ee-prototype.wmflabs.org @login

Feature: Create new topic logged in

For now this test is assumed to run against http://ee-prototype.wmflabs.org/ only
It requires VisualEditor not be in place, the cldr extension, a "Flow QA" page, and the "Selenium user".
If VisualEditor is enabled for Selenium_user, the flow_page definitions have to change.

Background:
  Given I am logged in

  Scenario: Add new Flow topic
    Given I have created a Flow topic
      And the author link is visible
      And the talk and contrib links are not visible
    When I hover over the author link
      Then links to talk and contrib should be visible

  Scenario: Block
    Given I am on Flow page
    When I hover over the author link
    Then I should see a Block User link

  Scenario: Actions
    Given I am on Flow page
    When I hover over the Actions link
      And I click Actions
    Then I should see a Hide button
      And I should see a Delete button
      And I should see a Suppress button
