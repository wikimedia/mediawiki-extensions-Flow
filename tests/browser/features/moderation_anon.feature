@chrome @firefox @internet_explorer_10 @login
Feature: Headers

  Assumes Flow is enabled for the User_talk namespace.

  Scenario: Deleted topics not shown to anonymous users
    Given I am on a Flow page with a deleted post with heading "DeleteAnonymousTest"
      And I am anonymous
    Then the top post should not have a heading which contains "DeleteAnonymousTest"
