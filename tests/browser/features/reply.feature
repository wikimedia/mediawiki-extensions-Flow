@chrome @clean @firefox @internet_explorer_10 @login
Feature: Replying

  Background:
    Given I am logged in
      And I am on Flow page

  @en.wikipedia.beta.wmflabs.org @test2.wikipedia.org
  Scenario: I can reply
    Given I have created a Flow topic with title "Reply test"
      And I reply with comment "Boom boom shake shake the room"
    Then the top post's first reply contains the text "Boom boom shake shake the room"

  @en.wikipedia.beta.wmflabs.org @test2.wikipedia.org
  Scenario: Replying updates watched state
    Given I have created a Flow topic with title "Reply watch test"
      And I am not watching my new Flow topic
    When I reply with comment "I want to watch this title"
    Then I should see an unwatch link on the topic

  # Broken due to bug 69412
  @wip
  Scenario: Canceling reply leaves usable form
    Given I have created a Flow topic with title "Reply watch test"
      And I start a reply with comment "my form lies over the ocean"
      And I click the Preview button
      # Create topic then click its Reply doesn't have a cancel button (bug 69412), so this fails.
      And I click the Cancel button and confirm the dialog
      And I start a reply with comment "bring back my form to me"
     Then I should see the topic reply form
