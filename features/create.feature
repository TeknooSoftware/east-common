Feature: Create an element, with slug or not stored into a the dbms server via an HTTP request

  Scenario: Create a object from an HTTP request multi parts
    Given I have DI With Symfony initialized
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/my_object/new" with "my_object%5Bname%5D=foo&my_object%5Bslug%5D=bar"
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_object/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar","saved":"foo"}'

  Scenario: Create a timestampable object from an HTTP request multi parts
    Given I have DI With Symfony initialized
    And a twig templating engine
    And set current datetime to "2022-08-14 01:02:03"
    When Symfony will receive the POST request "https://foo.com/my_object_timestampable/new" with "my_object%5Bname%5D=foo&my_object%5Bslug%5D=bar"
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_object_timestampable/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar","saved":"foo"}'
    And the date in object must be "2022-08-14 01:02:03"

  Scenario: Create a object from a JSON request
    Given I have DI With Symfony initialized
    And a twig templating engine
    When Symfony will receive the JSON request "https://foo.com/api/my_object/new" with '{"name": "foo", "slug": "bar"}'
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_object/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar","saved":"foo"}'

  Scenario: Create a timestampable object a JSON request
    Given I have DI With Symfony initialized
    And a twig templating engine
    And set current datetime to "2022-08-14 01:02:03"
    When Symfony will receive the JSON request "https://foo.com/api/my_object_timestampable/new" with '{"name": "foo", "slug": "bar"}'
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_object_timestampable/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar","saved":"foo"}'
    And the date in object must be "2022-08-14 01:02:03"
