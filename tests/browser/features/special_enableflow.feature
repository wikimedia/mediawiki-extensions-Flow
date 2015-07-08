@chrome @firefox @internet_explorer_10
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Flow Special:EnableFlow enables new flow boards




  Background:
    Given I am logged in

  Scenario: Enabling a Flow page
    When I navigate to enable flow page
      And I enable a new Flow board
    Then I get confirmation for enabling a new Flow board
      And I click on the new Flow board link
      And The page I am on is a Flow board
