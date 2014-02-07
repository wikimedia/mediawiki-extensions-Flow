

Scenario: Actions menu History
  Given I am on Flow page
    And I add a Topic
    And I add 3 comments to a Topic
  When I click Actions menu for the Topic
    And I click History from the Actions menu
    And I click 3 comments were added
  Then The comment history should be visible
    And the 3rd comment should have a green bar on the left.  (Note: this does not work as expected as of 7 Feb: https://bugzilla.wikimedia.org/show_bug.cgi?id=61046)

