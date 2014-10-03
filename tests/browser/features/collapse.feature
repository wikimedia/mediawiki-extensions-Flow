@chrome @clean @ee-prototype.wmflabs.org @en.wikipedia.beta.wmflabs.org @firefox @internet_explorer_10 @login @test2.wikipedia.org
Feature: Topic collapsing

  Background:
    Given I am logged in
      And I am on Flow page

      And I have created a Flow topic with title "Non-Moderated Topic"

      And I have created a Flow topic with title "Hidden Topic"
      And I click the Topic Actions link
      And I click the Hide topic button
      And I give reason for hiding as being "Test collapsing moderated posts"
      And I click Hide topic
      And I do not see the dialog box

  Scenario: Small topics view
    Given I am on Flow page
    When I switch from Topics and posts view to Small topics view
    Then I should see the title of the first topic
      And I should not see the comments of the first topic

  Scenario: Topics only view
    Given I am on Flow page
    When I switch from Topics and posts view to Topics only view
    Then I should see the title of the first topic
      And I should not see the comments of the first topic

  Scenario: Topics and posts view
    Given I am on Flow page
    # Complete mode cycle
    When I switch from Topics and posts view to Topics only view
      And I switch from Topics only view to Topics and posts view
    Then I should see the title of the first topic
      And I should see the comments of the first topic

  Scenario: Collapse override on a topic is forgotten every time the mode is switched
    Given I am on Flow page
    When I click the first topic
      And I switch from Topics and posts view to Topics only view
    # This "Then" would be the same regardless of whether it forgot
    # the override, since the override matches the new default state.
    # However, it doesn't hurt to assert it.
    Then I should see the title of the first topic
      And I should not see the comments of the first topic

    When I click the first topic
      And I switch from Topics only view to Small topics view
    Then I should see the title of the first topic
      And I should not see the comments of the first topic

    # Click it twice in a row, to test the new default state (everything
    # shows) still takes effect even though the user explicitly opened
    # and re-closed it on small topics view
    When I click the first topic
      And I click the first topic
      And I switch from Small topics view to Topics and posts view
    Then I should see the title of the first topic
      And I should see the comments of the first topic
