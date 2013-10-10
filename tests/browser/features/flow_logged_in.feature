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
