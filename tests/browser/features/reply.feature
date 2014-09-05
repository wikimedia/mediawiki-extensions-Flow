@chrome @clean @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Replying

  Background:
    Given I am logged in
      And I am on Flow page

  @phantomjs
  Scenario: I can reply
    Given I have created a Flow topic with title "Reply test"
      And I reply with comment "Boom boom shake shake the room"
    Then the top post's first reply contains the text "Boom boom shake shake the room"

  @phantomjs
  Scenario: Replying updates watched state
    Given I have created a Flow topic with title "Reply watch test"
      And I am not watching my new Flow topic
    When I reply with comment "I want to watch this title"
    Then I should see an unwatch link on the topic

# TODO maybe should test simple Cancelling reply as well.

  Scenario: Previewing reply, continue editing, then cancel leaves usable form
    Given I have created a Flow topic with title "Reply preview test"
      And I start a reply with comment "my form lies over the ocean"
      And I click the Preview button
      And I click the Keep editing button
      And I click the Cancel button and confirm the dialog
      And I start a reply with comment "bring back my form to me"
     Then I should see the topic reply form
