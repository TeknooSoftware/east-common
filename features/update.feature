Feature: Update an element, with slug or not stored into a the dbms server via an HTTP request
  
  Scenario: Update a type
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Object\Type" with id "foo" and '{"name":"foo","template":"bar","blocks":[]}'
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/admin/type/edit/foo" with "type%5Bname%5D=foo2&type%5Btemplate%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","template":"bar3","blocks":[]}'

  Scenario: Update a content
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Doctrine\Object\Content" with id "foo" and '{"author":null,"title":"foo","subtitle":"bar","slug":"foo"}'
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/admin/content/edit/foo" with "content%5Btitle%5D=foo2&content%5Bsubtitle%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"author":null,"title":"foo2","subtitle":"bar3","slug":"foo","type":null,"parts":"{}","tags":[],"description":null}'

  Scenario: Update an item
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Doctrine\Object\Item" with id "foo" and '{"name":"foo","slug":"foo","content":null,"position":1,"location":"bar"}'
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/admin/item/edit/foo" with "item%5Bname%5D=foo2&item%5Blocation%5D=bar3&item%5Bposition%5D=1"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"foo","content":null,"position":1,"location":"bar3","hidden":false,"parent":null,"children":[]}'