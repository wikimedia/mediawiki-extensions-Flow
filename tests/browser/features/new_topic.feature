@chrome @firefox @internet_explorer_10
Feature: Creating a new topic

  Scenario: Add new Flow topic as anonymous user
    When I have created a Flow topic with title "Anonymous user topic creation"
    Then the top post should have a heading which contains "Anonymous user topic creation"
    And the top post should have content which contains "Anonymous user topic creation"

  Scenario: Add new Flow topic with topic-title-wikitext
    Given I am logged in
    And I am on a new board
    When I have created a Flow topic containing the wikitext "[[Main Page]] [[Red link cIIBeqoNg8Bxo]] [[Media:Earth.jpg]] [http://example.com Example]"
    Then the top post should have a heading which contains "\[http://example.com Example\]"
    And there should be a link to the main page in the first topic title
    And there should be a red link in the first topic title
