@chrome @firefox @internet_explorer_10
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Thank author of a Flow post

  Scenario: Anon does not see Thank button
    Given the "Talk:Flow QA" page has a new unmoderated topic created by me
      And I am on Flow page
    Then I should not see a Thank button

  @login
  Scenario: Thank the user
    Given I am logged in
      And the most recent topic on "Talk:Flow QA" is written by another user
      And I am on Flow page
      And I see a Thank button
    When I click on the Thank button
    Then I should see the Thank button be replaced with Thanked button

  @login
  Scenario: I cannot thank my own post
    Given I am logged in
      And the "Talk:Flow QA" page has a new unmoderated topic created by me
      And I am on Flow page
    Then I should not see a Thank button
