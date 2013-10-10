@en.wikipedia.beta.wmflabs.org @login

Feature: Create new topic logged in

For now this test is assumed to run against http://en.wikipedia.beta.wmflabs.org/ only
It requires VisualEditor, the cldr extension, a "Flow QA" page, and the "Selenium user".

Background:
  Given I am logged in

  Scenario: Add new Flow topic
    Given I have created a VisualEditor Flow topic
      And the author link is visible
      And the talk and contrib links are not visible
    When I hover over the author link
      Then links to talk and contrib should be visible
