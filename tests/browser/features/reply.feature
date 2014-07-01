@chrome @clean @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Replying

  Background:
    Given I am logged in
      And I am on Flow page

  @wip
  Scenario: Reply button starts off disabled
    Given I have created a Flow topic with title "Reply disabled button test"
      And I click the reply input box
      And the reply button is disabled

  Scenario: I can reply
    Given I have created a Flow topic with title "Reply test"
      And I reply with comment "Boom boom shake shake the room"
    Then the top post's first reply contains the text "Boom boom shake shake the room"
