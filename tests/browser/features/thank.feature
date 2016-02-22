@chrome @firefox @internet_explorer_10
@vagrant
@en.wikipedia.beta.wmflabs.org
Feature: Thank author of a Flow post

  Scenario: Anon does not see Thank button
    Given there is a new topic
    When I am on Flow page
    Then I should not see a Thank button

  Scenario: Thank the user
    Given I am logged in
    And the most recent topic on "Talk:Flow QA" is written by another user
    And I am on Flow page
    When I click on the Thank button
    Then I should see the Thank button be replaced with Thanked button

  Scenario: I cannot thank my own post
    Given there is a new topic created by me
    And I am logged in
    When I am on Flow page
    Then I should not see a Thank button
