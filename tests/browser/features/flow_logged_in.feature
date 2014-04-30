@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Create new topic logged in

  It requires the cldr extension, a "Flow QA" page, and a "Selenium user" who has
  permission to Delete (usually 'sysop'/administrator user right) and to Suppress
  (usually the 'oversight' user right).
  If the Selenium_user's Flow editor is VisualEditor, then the flow_page
  definitions have to change.

  Background:
    Given I am logged in

  Scenario: Add new Flow topic
    Given I have created a Flow topic
      And the author link is visible
      And the talk and contrib links are not visible
    When I hover over the author link
      Then links to talk and contrib should be visible

  Scenario: Post Actions
    Given I am on Flow page
    When I click the Post Actions link
    Then I should see a Hide button
      And I should see a Delete button
      And I should see a Suppress button

  Scenario: Topic Actions
    Given I am on Flow page
    When I click the Topic Actions link
    Then I should see a Hide topic button
      And I should see a Delete topic button
      And I should see a Suppress topic button
