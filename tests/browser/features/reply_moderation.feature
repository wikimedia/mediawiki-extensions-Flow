@chrome @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Reply moderation

  Background:
    Given I am logged in
        And I am on Flow page

  Scenario: Hiding a comment
    Given I have created a Flow topic with title "Hide comment test"
      And I add 3 comments to the Topic
    When I click the Post Actions link on the 3rd comment on the topic
        And I click Hide comment button
        And I see a dialog box
        And I give reason for hiding as being "Shhhh!"
        And I click the Hide button in the dialog
    Then the 3rd comment should be marked as hidden
        And the content of the 3rd comment should not be visible
