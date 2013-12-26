@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Collapse views

  Scenario: Small view
    Given I am on Flow page
    When I click Small view
      And the page renders in 1 seconds
    Then I should see in Flow topics Title of Flow Topic
      And I should not see in Flow topics Body of Flow Topic
      And I should not see in Flow topics started this topic
      And I should see in Flow topics by 1 user

  Scenario: Collapse view
    Given I am on Flow page
    When I click Collapse view
      And the page renders in 1 seconds
    Then I should see in Flow topics Title of Flow Topic
      And I should not see in Flow topics Body of Flow Topic
      And I should see in Flow topics started this topic
      And I should not see in Flow topics by 1 user

  Scenario: Full view
    Given I am on Flow page
    When I click Full view
      And the page renders in 1 seconds
    Then I should see in Flow topics Title of Flow Topic
      And I should see in Flow topics Body of Flow Topic
      And I should see in Flow topics started this topic
      And I should see in Flow topics comment
      And I should see in Flow topics Body of Flow Topic
      And I should not see in Flow topics by 1 user


