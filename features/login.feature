Feature: Login user on a website

  Scenario: Login user with a salt but with bad login
    Given I have DI With Symfony initialized
    And a twig templating engine
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=bar@foo&_password=foobar"
    Then The client must accept a response
    And no session must be opened
    And It is redirect to "/user/login"

  Scenario: Login user with a salt but with bad credential
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a legacy user with password "testtest"
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=foobar"
    Then The client must accept a response
    And no session must be opened
    And It is redirect to "/user/login"

  Scenario: Login user with a salt but with good credential
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a legacy user with password "testtest"
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a legacy session must be opened
    And the user must been converted to sodium and not have salt
    And It is redirect to "/user"

  Scenario: Login user with sodium but with bad credential
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a user with password "testtest"
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=foobar"
    Then The client must accept a response
    And no session must be opened
    And It is redirect to "/user/login"

  Scenario: Login user with sodium but with good credential
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a user with password "testtest"
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a session must be opened
    And It is redirect to "/user"
