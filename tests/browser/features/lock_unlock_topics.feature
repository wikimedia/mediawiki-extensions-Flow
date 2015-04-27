@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @login @test2.wikipedia.org
Feature: Lock and unlock topics

  Background:
      Given I am logged in

  @wip
  Scenario: Locked topics have no reply links
    Given I am on Flow page
        And I have created a Flow topic
        And the top post has been locked
    When I expand the top post
    Then the original message for the top post should have no reply link
        And the original message for the top post should have no edit link

  @internet_explorer_10
  Scenario: Locking a topic and then changing your mind
    Given I am on Flow page
        And I have created a Flow topic
    When I hover on the Topic Actions link
        And I click the Lock topic button
        And I cancel the lock/unlock topic form
    Then the top post should be an open discussion
        And I should not see the lock/unlock form

  @internet_explorer_10
  Scenario: Locking a topic
    Given I am on Flow page
        And I have created a Flow topic
    When I hover on the Topic Actions link
        And I click the Lock topic button
        And I type "This is a bikeshed" as the reason
        And I submit the lock/unlock topic form
    Then the top post should be a locked discussion
        And the reason of the first topic should be "This is a bikeshed"
        And the content of the top post should be visible

  # Close-then-unlock doesn't work in IE, it caches the API response (bug 69160).
  Scenario: Opening a topic
    Given I am on Flow page
        And I have created a Flow topic
        And the top post has been locked
        And I hover on the Topic Actions link
        And I click the Unlock topic button
    When I type "Fun discussion" as the reason
        And I submit the lock/unlock topic form
    Then the top post should be an open discussion
        And the content of the top post should be visible
