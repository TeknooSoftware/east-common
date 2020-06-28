Feature: Delete an element, from the dbms server via an HTTP request
  Scenario: Delete a type
    Given I have DI With Symfony initialized
    When The server will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response

  Scenario: Delete a content
    Given I have DI With Symfony initialized
    When The server will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response

  Scenario: Delete an item
    Given I have DI With Symfony initialized
    When The server will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response