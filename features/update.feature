Feature: Update an element, with slug or not stored into a the dbms server via an HTTP request
  
  Scenario: Update a type
    Given I have DI With Symfony initialized
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/type/update/foo" with "foo:bar,bar:foo"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Update a content
    Given I have DI With Symfony initialized
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/content/update/foo" with "foo:bar,bar:foo"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Update an item
    Given I have DI With Symfony initialized
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/item/update/foo" with "foo:bar,bar:foo"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form "foo:bar,bar:foo"