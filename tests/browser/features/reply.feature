@chrome @clean @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Replying

  Background:
    Given I am logged in
      And I am on Flow page

  Scenario: I can reply
    Given I have created a Flow topic with title "Reply test"
      And I reply with comment "Boom boom shake shake the room"
    Then the top post's first reply contains the text "Boom boom shake shake the room"

  Scenario: Replying updates watched state
    Given I have created a Flow topic with title "Reply watch test"
      And I am not watching my new Flow topic
    When I reply with comment "I want to watch this title"
    Then I should see an unwatch link on the topic

  Scenario: Canceling reply leaves usable form
    Given I have created a Flow topic with title "Reply watch test"
      And I start a reply with comment "my form lies over the ocean"
      And I click the Preview button
      And I click the Cancel button and confirm the dialog
      And I start a reply with comment "bring back my form to me"
     Then I should see the topic reply form
