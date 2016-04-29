@internet_explorer_10
@vagrant
@en.wikipedia.beta.wmflabs.org
Feature: Replying

  Scenario: I can reply
    Given there is a new topic
    And I am on Flow page
    When I reply with comment "hi there"
    Then the top post's first reply should contain the text "hi there"

  @chrome @firefox
  Scenario: Replying updates watched state
    Given there is a new topic created by me
    And I am logged in
    And I am on Flow page
    And I am not watching my new Flow topic
    When I reply with comment "I want to watch this title"
    Then I should see an unwatch link on the topic
