@chrome @clean @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Moderation

  Assumes Flow is enabled for the User_talk namespace.

  Background:
    Given I am logged in
        And I am on Flow page

  Scenario: Deleting a topic
    Given I have created a Flow topic with title "Deletemeifyoudare"
    When I hover on the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for deletion as being "He's a naughty boy"
        And I click Delete topic
    Then the top post should be marked as deleted

  Scenario: Cancelling a dialog without text
    Given I have created a Flow topic with title "Testing cancel deletion of topic"
    When I hover on the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I cancel the dialog
    Then I do not see the dialog box

  Scenario: Cancelling a dialog with text
    Given I have created a Flow topic with title "Testing cancel deletion of topic"
    When I hover on the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for deletion as being "About to change my mind"
        And I cancel the dialog
        And I confirm
    Then I do not see the dialog box
