@chrome @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @phantomjs @test2.wikipedia.org
Feature: Creating a new topic

  Background:
    Given I am on Flow page

  Scenario: Cannot create a new topic without content
    When I type "Anonymous user topic creation test" into the new topic title field
    Then the Save New Topic button should be disabled

  Scenario: Add new Flow topic as anonymous user
    When I have created a Flow topic with title "Anonymous user topic creation"
    # TODO the terminology below is terrible, posts don't have headings. It's the top topic's title and first post.
    Then the top post should have a heading which contains "Anonymous user topic creation"
      And the top post should have content which contains "Anonymous user topic creation"
      And the new topic should be in the Recent Changes page
