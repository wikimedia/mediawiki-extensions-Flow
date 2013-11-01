@en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Edit title

  Scenario: Click edit title
    Given I am on Flow page
    When I hover over the topic post
      And I click the pencil icon
    Then the title text field should be visible
      And the Change Title button should be enabled
      And the Cancel button should be visible