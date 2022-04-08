Feature: Create an element, with slug or not stored into a the dbms server via an HTTP request

  Scenario: Create a object
    Given I have DI With Symfony initialized
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/my_object/new" with "my_object%5Bname%5D=foo&my_object%5Bslug%5D=bar"
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_object/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar"}'
