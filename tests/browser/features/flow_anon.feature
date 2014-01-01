@test2.wikipedia.org @en.wikipedia.beta.wmflabs.org @ee-prototype.wmflabs.org

Feature: Create new topic anonymous

  Scenario: Add new Flow topic
    Given I am on Flow page
    When I create a Topic: <Déjà vu 北京 <tag & "OK" 'end' [[Main_Page|main]] {{SITENAME}}> in Flow new topic
      And I create a Body: <Déjà vu 北京 <tag & "OK" 'end' [[Main_Page|main]] {{SITENAME}}> into Flow body
      And I click New topic save
    Then the Flow page should contain Topic: <Déjà vu 北京 <tag & "OK" 'end' [[Main_Page|main]] {{SITENAME}}>
      And the Flow page should contain Body: <Déjà vu 北京 <tag & "OK" 'end' [[Main_Page|main]] {{SITENAME}}>

  Scenario: Anon does not see block or actions
    Given I am on Flow page
    When I see a flow creator element
    Then I do not see an actions link
      And I do not see a block user link
