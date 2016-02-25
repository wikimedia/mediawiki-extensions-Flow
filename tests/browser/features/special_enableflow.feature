@chrome @firefox @internet_explorer_10
@vagrant
Feature: Flow Special:EnableFlow enables new flow boards

  Assumes Template:Wikitext_talk_page_converted_to_Flow exists
  and returns a link to the archive page.

  Background:
    Given I am logged in
    And I navigate to enable flow page

  Scenario: Enabling a new Flow page
    When I enable a new Flow board
    Then I get confirmation for enabling a new Flow board
    And I click on the new Flow board link
    And The page I am on is a Flow board

  Scenario: Enabling a new Flow page with a custom header
    When I enable a new Flow board with a custom header
    Then I get confirmation for enabling a new Flow board
    And I click on the new Flow board link
    And The page I am on is a Flow board
    And I see the custom header

  @integration
  Scenario: Enabling a Flow page on existing page
    Given I have an existing talk page
    When I enable a new Flow board on the talk page
    Then I get confirmation for enabling a new Flow board
    And I click on the new Flow board link
    And The page I am on is a Flow board
    And the board description contains the templates from my talk page
    And I click the archive link
    And The archive contains the original text
