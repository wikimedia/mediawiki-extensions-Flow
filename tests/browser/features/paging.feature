@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Navigate to older topics on pages with paging

  @clean
  Scenario: Infinite scrolling
    Given I am on Flow page
    When I scroll down to the bottom of the page
    # Alternatively - Then I can keep scrolling down 2 times to see older topics
    Then I can keep scrolling down to see older topics

  @clean @login
  Scenario: Scroll to see older topics that are initially not displayed
    Given I am on Flow page
      And I am logged in
      And I have created a topic and note the content
      And I have created 11 topics
    When I refresh the page
    Then I do not see the first topic
      And I scroll down to the bottom of the page
      And older topics are loaded in 10 seconds
      And I see the first topic
