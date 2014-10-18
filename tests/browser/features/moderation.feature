@chrome @firefox @internet_explorer_10 @login
Feature: Moderation

  Background:
    Given I am logged in
        And I am on Flow page

  Scenario: Deleting a topic
    Given I have created a Flow topic with title "Deletemeifyoudare"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give as reason for moderation "He's a naughty boy"
        And I click the dialog's Delete button
    Then the first topic should be moderated as deleted

  Scenario: Suppressing a topic
    Given I have created a Flow topic with title "Suppressmeifyoudare"
    When I click the Topic Actions link
        And I click the Suppress topic button
        And I see a dialog box
        And I give as reason for moderation "Quelling the peasants"
        And I click the dialog's Suppress button
    Then the first topic should be moderated as suppressed

  Scenario: Cancelling a dialog without text
    Given I have created a Flow topic with title "Testing cancel deletion of topic"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
    Then the dialog's Delete button should be disabled
    When I cancel the dialog
    Then I do not see the dialog box

  Scenario: Cancelling a dialog with text
    Given I have created a Flow topic with title "Testing cancel deletion of topic"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give as reason for moderation "About to change my mind"
        And I cancel the dialog
        And I confirm
    Then I do not see the dialog box
