@chrome @firefox @internet_explorer_10
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Suppress

  Assumes Flow is enabled on Talk:Flow_QA

  Background:
    Given I am logged in
        And I am on Flow page

  Scenario: Suppressing a topic
    Given I have created a Flow topic with title "suppress-topic"
    When I suppress the first topic with reason "I suppress you"
    Then the top post should be marked as suppressed

  Scenario: Restoring a topic
    Given I have created a Flow topic with title "suppress-restore-topic"
    When I suppress the first topic with reason "I suppress you temporarily"
        And I undo the suppression
    Then I see the topic "suppress-restore-topic" on the board
        And everybody sees the topic "suppress-restore-topic" on the board

  Scenario: A suppressed topic is not in board history
    Given I have created a Flow topic with title "suppress-not-in-history"
        And I suppress the first topic
    When I visit the board history page
    Then I see the following entries in board history
        |action              |topic                  |
        |suppressed the topic|suppress-not-in-history|
        |created the topic   |suppress-not-in-history|
    When I log out
        And I visit the board history page
    Then I do not see the following entries in board history
        |action              |topic                  |
        |suppressed the topic|suppress-not-in-history|
        |created the topic   |suppress-not-in-history|

  Scenario: A suppressed and restored topic is in board history
    Given I have created a Flow topic with title "suppress-restore-in-history"
        And I have suppressed and restored the first topic
    When I visit the board history page
    Then I see the following entries in board history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|
        |created the topic   |suppress-restore-in-history|
    When I log out
        And I visit the board history page
    Then I see the following entries in board history
        |action              |topic                      |
        |created the topic   |suppress-restore-in-history|
    Then I do not see the following entries in board history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|

  Scenario: A suppressed and restored topic is in topic history
    Given I have created a Flow topic with title "suppress-restore-in-history"
        And I have suppressed and restored the first topic
    When I visit the topic history page
    Then I see the following entries in topic history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|
        |created the topic   |suppress-restore-in-history|
    When I log out
        And I am on Flow page
        And I visit the topic history page
    Then I see the following entries in topic history
        |action              |topic                      |
        |created the topic   |suppress-restore-in-history|
    Then I do not see the following entries in topic history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|
