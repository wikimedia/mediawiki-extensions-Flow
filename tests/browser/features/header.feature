@chrome @ee-prototype.wmflabs.org @firefox @internet_explorer_10 @login @wip
Feature: Headers

  Background:
    Given I am logged in

  Scenario: No header on a new board
    Given I am on a new board
    Then The header should say "This talk page currently has no header"

  Scenario: Edit header form on a new board
    Given I am on a new board
    When I click the edit header link
    Then I should see the edit header form

  Scenario: Edit header on a new board
    Given I am on a new board
    When I click the edit header link
      And I type "test12345" into the header textbox
      And I click Save
    Then The header should say "test12345"
