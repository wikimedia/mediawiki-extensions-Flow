@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Thank author of a flow post

  @clean
  Scenario: Anon does not see Thank button
    Given I am on Flow page
    When I see a flow creator element
    Then I do not see a Thank button
      And I do not see a Thanked button

  @clean @login
  Scenario: Thank the user
  # Assuming a post by another non-anon user exists
    Given I am logged in
      And I am on Flow page
      And I see a Thank button
    When I click on the Thank button
    Then I should see the Thank button be replaced with Thanked button

  @clean @login
  Scenario: I cannot thank my own post
    Given I am logged in
      And I have created a Flow topic
    Then I should not see the Thank button for that post
