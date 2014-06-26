@chrome @firefox @internet_explorer_10 @login
Feature: Headers

  Assumes Flow is enabled for the User_talk namespace.

  Background:
    Given I am logged in
        And I am on Flow page

  Scenario: Hiding a comment
      And I create a Flow topic with title "Hide comment test"
      And I add 3 comments to the Topic
    When I click the Post Actions link on the 3rd comment on the topic
        And I click Hide comment button
        And I see a dialog box
        And I give reason for hiding as being "Shhhh!"
        And I click the Hide button in the dialog
    Then the 3rd comment should be marked as hidden
