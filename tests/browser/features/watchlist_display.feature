@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org @login

Feature: Watchlist display

  Background:
  Given I am logged in

  Scenario: Flow page is on watchlist
    Given I am on Flow page
      And Watchlist is checked
    When I navigate to the Watchlist
    Then the Watchlist page has a link to the Flow page