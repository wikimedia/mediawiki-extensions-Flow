@chrome @clean @firefox @internet_explorer_10 @login
Feature: Topic pages

  Scenario: Topic pages do not collapse [H9]
    Given I am on Flow page
      And I have created a Flow topic with title "Topic page test"
      And I switch from Topics and posts view to Small topics view
    When I click the Topic Actions link
      And I click Permalink from the Actions menu
      And the page has re-rendered
    Then the content of the top post should be visible
