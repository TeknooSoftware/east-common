Feature: Create an element, with slug or not stored into a the dbms server via an HTTP request

  Scenario: Create a type
    Given I have DI With Symfony initialized
    And I register a router
    And a Type Loader
    And a Type Writer
    And a Endpoint able to render form and create a type
    And The router can process the request "#/admin/type/new#is" to controller "newTypeEndPoint"
    When The server will receive the POST request "https://foo.com/admin/type/new" with "foo:bar,bar:foo"
    Then The client must accept a response
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Create a content
    Given I have DI With Symfony initialized
    And I register a router
    And a Content Loader
    And a Content Writer
    And a Endpoint able to render form and create a content
    And The router can process the request "#/admin/content/new#is" to controller "newContentEndPoint"
    When The server will receive the POST request "https://foo.com/admin/type/new" with "foo:bar,bar:foo"
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Create an item
    Given I have DI With Symfony initialized
    And I register a router
    And a Item Loader
    And a Item Writer
    And a Endpoint able to render form and create a item
    And The router can process the request "#/admin/item/new#is" to controller "newItemEndPoint"
    When The server will receive the POST request "https://foo.com/admin/type/new" with "foo:bar,bar:foo"
    And I should get in the form "foo:bar,bar:foo"