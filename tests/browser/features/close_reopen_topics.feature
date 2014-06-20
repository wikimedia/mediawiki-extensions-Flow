@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @test2.wikipedia.org
Feature: Close and open topics

  Background:
      Given I am logged in

  Scenario: Closing a topic and then changing your mind
    Given I am on Flow page
        And I have created a Flow topic
    When I click the Topic Actions link
        And I click the Close topic button
        And I cancel the close/reopen topic form
        And the page renders in 1 seconds
    Then the top post is an open discussion
        And I do not see the close/reopen form

  Scenario: Closing a topic
    Given I am on Flow page
        And I have created a Flow topic
    When I click the Topic Actions link
        And I click the Close topic button
        And I type "This is a bikeshed" as the reason
        And I submit the close/reopen topic form
        And the page renders in 2 seconds
    Then the top post is a closed discussion
        And I expand the top post
        And the topic summary of the first topic is "This is a bikeshed"

  Scenario: Opening a topic
    Given I am on Flow page
        And I have created a Flow topic
        And the top post has been closed
        And I click the Topic Actions link
        And I click the Reopen topic button
    When I type "Fun discussion" as the reason
        And I submit the close/reopen topic form
        And the page renders in 2 seconds
    Then the top post is an open discussion
        And I expand the top post
        And the topic summary of the first topic is "Fun discussion"
