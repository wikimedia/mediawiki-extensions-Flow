@chrome @firefox @internet_explorer_10 @login
Feature: Headers

  Assumes Flow is enabled for the User_talk namespace.

  Background:
    Given I am logged in
        And I am on Flow page

  Scenario: Deleting a topic
      And I create a Flow topic with title "Deletemeifyoudare"
    When I click the Topic Actions link
        And I click the Delete topic button
        And I see a dialog box
        And I give reason for deletion as being "He's a naughty boy"
        And I click Delete topic
    Then the top post should be marked as deleted
