@chrome @firefox
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Opt-in Flow beta feature

  Depends on having $wgFlowEnableOptInBetaFeature = true
  and USER_TALK_NS not occupied by Flow.

  Background:
    Given I am logged in as a new user

  Scenario: Opt-in: I don't have a talk page
    When I enable Flow beta feature
    Then my talk page is a flow board

  Scenario: Opt-in: I have a wikitext talk page
    Given my talk page has wiktext content
    When I enable Flow beta feature
    Then my talk page is a flow board
    And my previous talk page is archived

  # Scenario: Opt-out: I don't have a talk page

  # Scenario: Opt-out: I have a wikitext talk page