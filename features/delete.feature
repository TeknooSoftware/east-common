Feature: Delete an element, from the dbms server via an HTTP request
  Scenario: Delete a type
    Given I have DI With Symfony initialized
    And I register a router
    And a symfony url generator
    And a symfony locator
    And a Type Loader
    And a Type Writer
    And a Endpoint able to render form and delete a type
    And The router can process the request "#/admin/type/delete/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "deleteTypeEndPoint"
    When The server will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response

  Scenario: Delete a content
    Given I have DI With Symfony initialized
    And I register a router
    And a symfony url generator
    And a symfony locator
    And a Content Loader
    And a Content Writer
    And a Endpoint able to render form and delete a content
    And The router can process the request "#/admin/content/delete/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "deleteContentEndPoint"
    When The server will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response

  Scenario: Delete an item
    Given I have DI With Symfony initialized
    And I register a router
    And a symfony url generator
    And a symfony locator
    And a Item Loader
    And a Item Writer
    And a Endpoint able to render form and delete a item
    And The router can process the request "#/admin/item/delete/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "deleteItemEndPoint"
    When The server will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response