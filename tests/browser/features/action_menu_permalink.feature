

Scenario: Actions menu Permalink
  Given I am on Flow page
  When I click Actions menu for the Topic
    And I click Permalink from the Actions menu
    And I add 3 comments to the Topic
    And I click Actions menu for the 3rd comment on the Topic
    And I click Permalink from the Actions menu
  Then the text of the 3rd comment on the Topic should be visible
    And the 3rd comment on the Topic should have a green bar on the left
