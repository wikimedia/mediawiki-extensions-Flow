@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Create new topic logged in

  It requires the cldr extension, a "Flow QA" page, and a "Selenium user" who has
  permission to Delete (usually 'sysop'/administrator user right) and to Suppress
  (usually the 'oversight' user right).
  If the Selenium_user's Flow editor is VisualEditor, then the flow_page
  definitions have to change.

  Background:
    Given I am logged in
    And I have created a Flow topic

  Scenario: Add new Flow topic and show author and block links
    Given the author link is visible
      And the talk to author link is not visible
      And the block author link is not visible
    When I hover over the author link
    Then the talk to author link is visible
      And the block author link is visible

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
