@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @login @test2.wikipedia.org
Feature: Close and open topics

  Background:
      Given I am logged in

  @internet_explorer_10
  Scenario: Closing a topic and then changing your mind
    Given I am on Flow page
        And I have created a Flow topic
    When I click the Topic Actions link
        And I click the Close topic button
        And I cancel the close/reopen topic form
        And the page has re-rendered
    Then the top post is an open discussion
        And I do not see the close/reopen form

  @internet_explorer_10
  Scenario: Closing a topic
    Given I am on Flow page
        And I have created a Flow topic
    When I click the Topic Actions link
        And I click the Close topic button
        And I type "This is a bikeshed" as the reason
        And I submit the close/reopen topic form
        And the page has re-rendered
    Then the top post is a closed discussion
        And the topic summary of the first topic is "This is a bikeshed"
        And the content of the top post should be visible

  # Close-then-reopen doesn't work in IE, it caches the API response (bug 69160).
  Scenario: Opening a topic
    Given I am on Flow page
        And I have created a Flow topic
        And the top post has been closed
        And I click the Topic Actions link
        And I click the Reopen topic button
    When I type "Fun discussion" as the reason
        And I submit the close/reopen topic form
        And the page has re-rendered
    Then the top post is an open discussion
        And the topic summary of the first topic is "Fun discussion"
        And the content of the top post should be visible
