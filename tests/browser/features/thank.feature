@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Thank author of a Flow post

  @clean
  Scenario: Anon does not see Thank button
    Given I am on Flow page
    When I see a flow creator element
    Then I do not see a Thank button
      And I do not see a Thanked button

  @clean @login
  Scenario: I cannot thank my own post
    Given I am logged in
      And I am on Flow page
      And I have created a Flow topic
    Then I should not see the Thank button for that post
