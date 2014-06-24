@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @test2.wikipedia.org
Feature: Small topics views

  Background:
    Given I am logged in
      And I am on Flow page
      And I create a regular topic
      And I create a hidden topic

  Scenario: Topics only view
    Given I am on Flow page
    When I switch to Topics only view
      And the page renders in 1 seconds
    Then I should see the title of the first regular topic
      And I should see the user who started the first regular topic
      And I should see the reply button of the first regular topic
      And I should see the number of comments of the first regular topic
      And I should see the activity time of the first regular topic

      And I should not see in Flow topics started this topic
      And I should not see in Flow topics comment

  Scenario: Small topics view
    Given I am on Flow page
    When I switch to Small topics view
      And the page renders in 1 seconds
    Then I should see in Flow topics Title of Flow Topic
      And I should not see in Flow topics Body of Flow Topic
      And I should see in Flow topics started this topic
      And I should see in Flow topics comment

  Scenario: Topics and posts view
    Given I am on Flow page
    When I switch to Topics and posts view
      And the page renders in 1 seconds
    Then I should see in Flow topics Title of Flow Topic
      And I should see in Flow topics Body of Flow Topic
      And I should see in Flow topics started this topic
      And I should see in Flow topics comment
