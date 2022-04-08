Feature: Delete an element, from the dbms server via an HTTP request
  Scenario: Delete an object
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a object with id "foo"
    When Symfony will receive the DELETE request "https://foo.com/my_object/delete/foo"
    Then The client must accept a response
    And It is redirect to "/my_objects/"
    And the last object updated must be deleted
