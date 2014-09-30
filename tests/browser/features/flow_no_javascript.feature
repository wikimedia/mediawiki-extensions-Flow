@custom-browser @en.wikipedia.beta.wmflabs.org @firefox @login @test2.m.wikipedia.org
Feature: Basic site for legacy devices

  Background:
    Given I am using user agent "Mozilla/4.0 (compatible; MSIE 5.5b1; Mac_PowerPC)"
      And I am on a Flow page without JavaScript

  Scenario: I post a new topic without JavaScript
    When I see the form to post a new topic
	  And I click Add topic no javascript
      And I enter a no javascript topic title of "Selenium no javascript title"
	  And I enter a no javascript topic body of "Selenium no javascript body"
      And I save a no javascript new topic
    Then the page contains my no javascript topic
      And the page contains my no javascript body

  Scenario: I can see form to reply to a topic without JavaScript
    Then I see the form to reply to a topic
		And the post new reply form has an add topic button
		And the post new reply form has a preview button
		And the post new reply form does not have a cancel button
