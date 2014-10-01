@chrome @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @phantomjs @test2.wikipedia.org
Feature: Creating a new topic

  Background:
    Given I am on Flow page

  Scenario: Cannot create a new topic without content
    When I type "Anonymous user topic creation test" into the new topic title field
    Then the Save New Topic button should be disabled

  Scenario: Add new Flow topic as anonymous user
    When I type "Anonymous user topic creation test" into the new topic title field
      And I type "Anon test." into the new topic content field
      And I click New topic save
    Then the top post should have a heading which contains "Anonymous user topic creation test"
      And the top post should have content which contains "Anon test."
    When I click the Topic Actions link
      And I click Permalink from the Actions menu
      And the page has re-rendered
      And I am viewing Topic page
    Then I see only one topic on the page
      And the top post should have a heading which contains "Anon test."

