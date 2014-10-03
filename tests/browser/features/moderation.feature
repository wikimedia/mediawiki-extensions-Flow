@chrome @firefox @internet_explorer_10 @login
Feature: Moderation

  Assumes Flow is enabled for the User_talk namespace.

  Background:
    Given I am logged in
        And I am on Flow page

  # Scenario: Hiding a topic
  # TODO - collapse.feature hides a topic, copy that. Anons can do it as well.
  # Given I hide a topic
  # Then I should see a moderated message on the first topic
  #   And I should see Undo in the first topic
  #   And I should not see the title of the first topic
  #   And I should not see the comments of the first topic
  # When I reload the page
  # Then I should not see hidden, deleted, or suppressed topics
  #
  # Scenario: Undo hiding a topic
  # Given I hide a topic
  # When I click Undo in the first topic
  # Then I should see my topic displayed normally
  # When I reload the page
  # Then I should see my topic displayed normally
  #
  # TODO: This displayed normally is the same code as in collapse.feature and steps.
  # Step "displayed normally"
  #   And I should see not a moderated message on my topic
  #   And I should see the title of my topic
  #   And I should see the comments of my topic

  Scenario: Deleting a topic
    Given I have created a Flow topic with title "Deletemeifyoudare"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for deletion as being "He's a naughty boy"
        And I click Delete topic
    Then the top post should be marked as deleted

  Scenario: Suppressing a topic
    Given I have created a Flow topic with title "Suppressmeifyoudare"
    When I click the Topic Actions link
        And I click the Suppress topic button
        And I see a dialog box
        And I give reason for suppression as being "Quelling the peasants"
        And I click Suppress topic
    Then the top post should be marked as suppressed

  Scenario: Cancelling a dialog without text
    Given I have created a Flow topic with title "Testing cancel deletion of topic"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I cancel the dialog
    Then I do not see the dialog box

  Scenario: Cancelling a dialog with text
    Given I have created a Flow topic with title "Testing cancel deletion of topic"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for suppression as being "About to change my mind"
        And I cancel the dialog
        And I confirm
    Then I do not see the dialog box
