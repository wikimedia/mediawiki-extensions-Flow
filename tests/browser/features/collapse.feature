@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @test2.wikipedia.org
Feature: Topic collapsing

  Background:
    Given I am logged in
      And I am on Flow page
      And I create a non-moderated topic

  Scenario: Small topics view
    Given I am on Flow page
    When I switch to Small topics view
      And the page renders in 1 seconds
    Then I should see the title of the first non-moderated topic
      And I should not see who started the first non-moderated topic
      And I should not see the comments of the first non-moderated topic

  Scenario: Topics only view
    Given I am on Flow page
    When I switch to Topics only view
      And the page renders in 1 seconds
    Then I should see the title of the first non-moderated topic
      And I should see who started the first non-moderated topic
      And I should not see the comments of the first non-moderated topic

  Scenario: Topics and posts view
    Given I am on Flow page
    When I switch to Topics and posts view
      And the page renders in 1 seconds
    Then I should see the title of the first non-moderated topic
      And I should see who started the first non-moderated topic
      And I should see the comments of the first non-moderated topic
