@chrome @firefox @internet_explorer_10 @login
Feature: Headers

  Assumes Flow is enabled for the User_talk namespace.

  Background:
    Given I am logged in
        And I am on Flow page

  @wip
  Scenario: Deleting a topic
      And I create a Flow topic with title "Deletemeifyoudare"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for deletion as being "He's a naughty boy"
        And I click Delete topic
    Then the top post should be marked as deleted

  Scenario: Suppressing a topic
      And I create a Flow topic with title "Suppressmeifyoudare"
    When I click the Topic Actions link
        And I click the Suppress topic button
        And I see a dialog box
        And I give reason for suppression as being "Quelling the peasants"
        And I click Suppress topic
    Then the top post should be marked as suppressed

  Scenario: Cancelling a dialog without text
      And I create a Flow topic with title "Testing cancel deletion of topic"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I cancel the dialog
    Then I do not see the dialog box

  Scenario: Cancelling a dialog with text
      And I create a Flow topic with title "Testing cancel deletion of topic"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for suppression as being "About to change my mind"
        And I cancel the dialog
        And I confirm
    Then I do not see the dialog box
