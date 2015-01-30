@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @login @test2.wikipedia.org
Feature: Create new topic logged in

  Background:
    Given I am logged in
    And I have created a Flow topic
    Then the topic should be visible with an id