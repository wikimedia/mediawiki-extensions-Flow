@chrome @firefox @internet_explorer_10 @phantomjs
@clean @login
@en.wikipedia.beta.wmflabs.org
Feature: Check the interface for anonymous users

  Scenario: Anon does not see block or actions
    Given I am on Flow page
      And I have created a Flow topic
      # which is not hidden (this is implicit from the above step)
    When I see a flow creator element
    Then the block author link should not be visible
