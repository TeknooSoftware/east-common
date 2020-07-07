Feature: Update an element, with slug or not stored into a the dbms server via an HTTP request
  
  Scenario: Update a type
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Object\Type" with id "foo" and '{"name":"foo","template":"bar","blocks":[]}'
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/type/edit/foo" with "type%5Bname%5D=foo2&type%5Btemplate%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","template":"bar3","blocks":[]}'

  Scenario: Update a content
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Doctrine\Object\Content" with id "foo" and '{}'
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/content/edit/foo" with "foo:bar,bar:foo"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form "foo:bar,bar:foo"

  Scenario: Update an item
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Doctrine\Object\Item" with id "foo" and '{}'
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/item/edit/foo" with "foo:bar,bar:foo"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form "foo:bar,bar:foo"