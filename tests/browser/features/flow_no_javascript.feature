@firefox
@en.wikipedia.beta.wmflabs.org
Feature: Basic site for legacy devices

  Background:
    Given I am using a nojs browser
    And I am on a Flow page without JavaScript

  Scenario: I post a new topic without JavaScript
    When I see the form to post a new topic
    And I click Add topic no javascript
    And I enter a no javascript topic title of "Selenium no javascript title"
    And I enter a no javascript topic body of "Selenium no javascript body"
    And I save a no javascript new topic
    Then the page contains my no javascript topic
    And the page contains my no javascript body

  Scenario: I reply to a topic without JavaScript
    When I see the form to reply to a topic
    And I enter a no javascript reply of "Selenium no javascript reply"
    And I save a no javascript reply
    Then the page contains my no javascript reply
