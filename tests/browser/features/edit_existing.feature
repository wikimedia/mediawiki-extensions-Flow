@clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Edit existing title

  Assumes that the test Flow page has at least two topics (with posts).

  Background:
    Given I am logged in

  Scenario: Edit an existing title
    Given I am on Flow page
    When I click the Edit title action
    Then I should be able to edit the title field with Title edited
      And I should be able to save the new title
      And the saved topic title should contain Title edited

  Scenario: Edit existing post
    Given I am on Flow page
    When I click Edit post
    Then I should be able to edit the post field with Post edited
      And I should be able to save the new post
      And the saved post should contain Post edited
