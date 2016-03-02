@chrome @firefox
@vagrant
Feature: Opt-in Flow beta feature

  Depends on having $wgFlowEnableOptInBetaFeature = true
  and NS_USER_TALK not occupied by Flow.

  Background:
    Given I am logged in as a new user

  @en.wikipedia.beta.wmflabs.org
  Scenario: Opt-in: I don't have a talk page
    When I enable Flow beta feature
    Then my talk page is a Flow board
    And a notification tells me about it

  @en.wikipedia.beta.wmflabs.org @integration
  Scenario: Opt-in: I have a wikitext talk page
    Given my talk page has wiktext content
    When I enable Flow beta feature
    Then my talk page is a Flow board
    And my flow board contains a link to my archived talk page
    And the board description contains the templates from my talk page
    And my previous talk page is archived

  Scenario: Opt-out: I didn't have a talk page
    Given I have Flow beta feature enabled
    When I disable Flow beta feature
    Then my Flow board is archived
    And my talk page is deleted without redirect

  Scenario: Opt-out: I had a wikitext talk page
    Given my talk page has wiktext content
    And I have Flow beta feature enabled
    When I disable Flow beta feature
    Then my wikitext talk page is restored
    And my Flow board is archived

  Scenario: Re-opt-in
    Given I have used the Flow beta feature before
    When I enable Flow beta feature
    Then my talk page is my old Flow board
    And my previous talk page is archived
