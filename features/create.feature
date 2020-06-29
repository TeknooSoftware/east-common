Feature: Create an element, with slug or not stored into a the dbms server via an HTTP request

  Scenario: Create a type
    Given I have DI With Symfony initialized
    When Symfony will receive the POST request "https://foo.com/admin/type/new" with "type%5Bname%5D=foo&type%5Btemplate%5D=bar"
    Then The client must accept a response
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Create a content
    Given I have DI With Symfony initialized
    When Symfony will receive the POST request "https://foo.com/admin/type/new" with "foo:bar,bar:foo"
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Create an item
    Given I have DI With Symfony initialized
    When Symfony will receive the POST request "https://foo.com/admin/type/new" with "foo:bar,bar:foo"
    And I should get in the form "foo:bar,bar:foo"