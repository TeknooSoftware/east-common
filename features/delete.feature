Feature: Delete an element, from the dbms server via an HTTP request
  Scenario: Delete a type
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Object\Type" with id "foo"
    When Symfony will receive the DELETE request "https://foo.com/admin/type/delete/foo"
    Then The client must accept a response
    And It is redirect to "/admin/types/"
    And the last object updated must be deleted

  Scenario: Delete a content
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Doctrine\Object\Content" with id "foo"
    When Symfony will receive the DELETE request "https://foo.com/admin/content/delete/foo"
    Then The client must accept a response
    And It is redirect to "/admin/contents/"
    And the last object updated must be deleted

  Scenario: Delete an item
    Given I have DI With Symfony initialized
    And a object of type "Teknoo\East\Website\Doctrine\Object\Item" with id "foo"
    When Symfony will receive the DELETE request "https://foo.com/admin/item/delete/foo"
    Then The client must accept a response
    And It is redirect to "/admin/items/"
    And the last object updated must be deleted