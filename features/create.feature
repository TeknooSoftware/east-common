Feature: Create an element, with slug or not stored into a the dbms server via an HTTP request

  Scenario: Create a type
    Given I have DI With Symfony initialized
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/type/new" with "type%5Bname%5D=foo&type%5Btemplate%5D=bar"
    Then The client must accept a response
    And An object "Type" must be persisted
    And It is redirect to "/admin/type/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","template":"bar","blocks":[]}'

  Scenario: Create a content
    Given I have DI With Symfony initialized
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/content/new" with "content%5Btitle%5D=foo&content%5Bsubtitle%5D=bar"
    Then The client must accept a response
    And An object "Content" must be persisted
    And It is redirect to "/admin/content/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"author":null,"title":"foo","subtitle":"bar","slug":"foo","type":null,"parts":"{}","tags":[],"description":null}'

  Scenario: Create an item
    Given I have DI With Symfony initialized
    And a templating engine
    When Symfony will receive the POST request "https://foo.com/admin/item/new" with "item%5Bname%5D=foo&item%5Blocation%5D=bar&item%5Bposition%5D=1"
    Then The client must accept a response
    And An object "Item" must be persisted
    And It is redirect to "/admin/item/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"foo","content":null,"position":1,"location":"bar","hidden":false,"parent":null,"children":[]}'