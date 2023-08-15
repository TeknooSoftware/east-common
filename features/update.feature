Feature: Update an element, with slug or not stored into a the dbms server via an HTTP request
  
  Scenario: Update an object from an HTTP request multi parts
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the POST request "https://foo.com/my_object/edit/foo" with "my_object%5Bname%5D=foo2&my_object%5Bslug%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'

  Scenario: Update an timestampable object from an HTTP request multi parts
    Given I have DI With Symfony initialized
    And a twig templating engine
    And set current datetime to "2022-08-14 01:02:03"
    And a timestampable object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the POST request "https://foo.com/my_object_timestampable/edit/foo" with "my_object%5Bname%5D=foo2&my_object%5Bslug%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'
    And the date in object must be "2022-08-14 01:02:03"

  Scenario: Update an timestampable object with real date from an HTTP request multi parts
    Given I have DI With Symfony initialized
    And a twig templating engine
    And set current datetime to "2022-08-14 01:02:03"
    And a timestampable object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the POST request "https://foo.com/my_object_timestampable/edit_real_date/foo" with "my_object%5Bname%5D=foo2&my_object%5Bslug%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'
    And the date in object must be newer than "2022-08-14 01:02:03"

  Scenario: Update an object from a JSON request
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the JSON request "https://foo.com/api/my_object/edit/foo" with '{"name": "foo2", "slug": "bar3"}'
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'

  Scenario: Update an timestampable object from a JSON request
    Given I have DI With Symfony initialized
    And a twig templating engine
    And set current datetime to "2022-08-14 01:02:03"
    And a timestampable object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the JSON request "https://foo.com/api/my_object_timestampable/edit/foo" with '{"name": "foo2", "slug": "bar3"}'
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'
    And the date in object must be "2022-08-14 01:02:03"

  Scenario: Update an timestampable object with real date from a JSON requests
    Given I have DI With Symfony initialized
    And a twig templating engine
    And set current datetime to "2022-08-14 01:02:03"
    And a timestampable object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the JSON request "https://foo.com/api/my_object_timestampable/edit_real_date/foo" with '{"name": "foo2", "slug": "bar3"}'
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'
    And the date in object must be newer than "2022-08-14 01:02:03"
