@chrome @firefox @internet_explorer_10
@vagrant
Feature: Suppress

  Assumes Flow is enabled on Talk:Flow_QA

  Background:
    Given I am logged in

  Scenario: Suppressing a topic
    Given there is a new topic with title "suppress-topic"
    And I am on Flow page
    When I suppress the first topic with reason "I suppress you"
    Then the top post should be marked as suppressed

  Scenario: Restoring a topic
    Given there is a new topic with title "suppress-restore-topic"
    And I am on Flow page
    When I suppress the first topic with reason "I suppress you temporarily"
    And I undo the suppression
    Then I see the topic "suppress-restore-topic" on the board
    And everybody sees the topic "suppress-restore-topic" on the board

  Scenario: A suppressed topic is not in board history
    Given there is a new topic with title "suppress-not-in-history"
    And I am on Flow page
    And I suppress the first topic
    When I visit the board history page
    Then I see the following entries in board history
        |action              |topic                  |
        |suppressed the topic|suppress-not-in-history|
    And I do not see the following entries in board history
        |action              |topic                  |
        |commented on        |suppress-not-in-history|
    When I log out
    And I visit the board history page
    Then I do not see the following entries in board history
        |action              |topic                  |
        |suppressed the topic|suppress-not-in-history|
        |commented on        |suppress-not-in-history|

  Scenario: A suppressed and restored topic is in board history
    Given there is a new topic with title "suppress-restore-in-history"
    And I am on Flow page
    And I have suppressed and restored the first topic
    When I visit the board history page
    Then I see the following entries in board history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|
        |commented on        |suppress-restore-in-history|
    When I log out
    And I visit the board history page
    Then I see the following entries in board history
        |action              |topic                      |
        |commented on        |suppress-restore-in-history|
    Then I do not see the following entries in board history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|

  Scenario: A suppressed and restored topic is in topic history
    Given there is a new topic with title "suppress-restore-in-history"
    And I am on Flow page
    And I have suppressed and restored the first topic
    When I visit the topic history page
    Then I see the following entries in topic history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|
        |commented on        |suppress-restore-in-history|
    When I log out
    And I am on Flow page
    And I visit the topic history page
    Then I see the following entries in topic history
        |action              |topic                      |
        |commented on        |suppress-restore-in-history|
    Then I do not see the following entries in topic history
        |action              |topic                      |
        |restored the topic  |suppress-restore-in-history|
        |suppressed the topic|suppress-restore-in-history|
