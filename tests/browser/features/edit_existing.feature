@chrome @firefox @internet_explorer_10
@vagrant
@en.wikipedia.beta.wmflabs.org
Feature: Edit existing title

  Background:
    Given there is a new topic with title "original title"

  Scenario: Edit an existing title
    Given I am on Flow page
    When I select the Edit title action
    And I edit the title field with "Title edited"
    And I save the new title
    Then the top post should have a heading which contains "Title edited"

  Scenario: Edit existing post
    Given I am logged in
    And I am on Flow page
    When I select Edit post
    And I edit the post field with "Post edited"
    And I save the new post
    Then the saved post should contain "Post edited"
