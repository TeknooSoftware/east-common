Feature: Test EastDataMapper with mutable, visitable and immutable objects

  Scenario: Create a mutable object using EastDataMapper
    Given I have DI With Symfony initialized
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/my_mutable_mapped/new" with "my_mutable_mapped_object%5Bname%5D=foo&my_mutable_mapped_object%5Bslug%5D=bar"
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_mutable_mapped/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar","saved":"foo"}'

  Scenario: Update a mutable object using EastDataMapper
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the POST request "https://foo.com/my_mutable_mapped/edit/foo" with "my_mutable_mapped_object%5Bname%5D=foo2&my_mutable_mapped_object%5Bslug%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'

  Scenario: Create a visitable object using EastDataMapper
    Given I have DI With Symfony initialized
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/my_visitable/new" with "my_visitable_object%5Bname%5D=foo&my_visitable_object%5Bslug%5D=bar"
    Then The client must accept a response
    And An object must be persisted
    And It is redirect to "/my_visitable/edit/[a-zA-Z0-9]+"
    When the client follows the redirection
    And I should get in the form '{"name":"foo","slug":"bar","saved":"foo"}'

  Scenario: Update a visitable object using EastDataMapper
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a visitable object with id "foo" and '{"name":"foo","slug":"bar","saved":"bar"}'
    When Symfony will receive the POST request "https://foo.com/my_visitable/edit/foo" with "my_visitable_object%5Bname%5D=foo2&my_visitable_object%5Bslug%5D=bar3"
    Then The client must accept a response
    And An object "foo" must be updated
    And I should get in the form '{"name":"foo2","slug":"bar3","saved":"foo"}'

