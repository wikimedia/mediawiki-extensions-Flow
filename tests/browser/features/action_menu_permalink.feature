@chrome @clean @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Actions menu Permalink

  Background:
    Given I am logged in
      And I am on Flow page
      And page has no ResourceLoader errors

  Scenario: Topic Actions menu Permalink
    Given I have created a Flow topic with title "Permalinktest"
    When I click the Topic Actions link
      And I click Permalink from the Actions menu
    Then I see only one topic on the page
      And the top post should have a heading which contains "Permalinktest"

  Scenario: Actions menu Permalink
    Given I have created a Flow topic with title "PermalinkReplyTest"
      And I add 3 comments to the Topic
    When I click the Post Actions link on the 3rd comment on the topic
      And I click the Post Actions link on the 3rd comment on the topic
      And I click Permalink from the 3rd comment Post Actions menu
    Then I see only one topic on the page
      And the highlighted comment should contain the text for the 3rd comment
