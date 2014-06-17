@chrome @firefox @internet_explorer_10 @login
Feature: Headers

  Assumes Flow is enabled for the User_talk namespace.

  Background:
    Given I am logged in

  Scenario: No header on a new board
    Given I am on a new board
    Then The header should say "This talk page currently has no header"

  Scenario: Edit header on a new board
    Given I am on a new board
    When I click the edit header link
      And I see the edit header form
      And I type "test12345" into the header textbox
      And I click Save
    Then The header should say "test12345"
