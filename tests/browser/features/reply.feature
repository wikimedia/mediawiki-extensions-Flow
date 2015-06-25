@chrome @firefox @internet_explorer_10
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Replying

  Background:
    Given I am logged in
      And I am on Flow page

  @phantomjs
  Scenario: I can reply
    Given I have created a Flow topic with title "Reply test"
    When I reply with comment "Boom boom shake shake the room"
    Then the top post's first reply should contain the text "Boom boom shake shake the room"

  @phantomjs
  Scenario: Replying updates watched state
    Given I have created a Flow topic with title "Reply watch test"
      And I am not watching my new Flow topic
    When I reply with comment "I want to watch this title"
    Then I should see an unwatch link on the topic
