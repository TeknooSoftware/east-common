Feature: Update an element, with slug or not stored into a the dbms server via an HTTP request
  
  Scenario: Update a type
    Given I have DI With Symfony initialized
    And I register a router
    And a Type Loader
    And a Type Writer
    And a Endpoint able to render form and update a type
    And The router can process the request "#/admin/type/update/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "updateTypeEndPoint"
    When The server will receive the POST request "https://foo.com/admin/type/update/foo" with "foo:bar,bar:foo"
    Then The client must accept a response
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Update a content
    Given I have DI With Symfony initialized
    And I register a router
    And a Content Loader
    And a Content Writer
    And a Endpoint able to render form and update a content
    And The router can process the request "#/admin/content/update/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "updateContentEndPoint"
    When The server will receive the POST request "https://foo.com/admin/type/update/foo" with "foo:bar,bar:foo"
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Update an item
    Given I have DI With Symfony initialized
    And I register a router
    And a Item Loader
    And a Item Writer
    And a Endpoint able to render form and update a item
    And The router can process the request "#/admin/item/update/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "updateItemEndPoint"
    When The server will receive the POST request "https://foo.com/admin/type/update/foo" with "foo:bar,bar:foo"
    And I should get in the form "foo:bar,bar:foo"