@custom-browser @en.m.wikipedia.beta.wmflabs.org @firefox @login @test2.m.wikipedia.org
Feature: Basic site for legacy devices

  Background:
    Given I am viewing the site in without JavaScript
      And I am on Flow page

  Scenario: I can see form to post a new topic without JavaScript
    Then I see the form to post a new topic
		And the post new topic form has an add topic button
		And the post new topic form has a preview button
		And the post new topic form does not have a cancel button

  Scenario: I can see form to reply to a topic without JavaScript
    Then I see the form to reply to a topic
		And the post new reply form has an add topic button
		And the post new reply form has a preview button
		And the post new reply form does not have a cancel button
