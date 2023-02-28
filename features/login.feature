Feature: Login user on a website

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

  Scenario: Login user with sodium but with good credential and 2FA set but not enabled
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a user with password "testtest"
    And an 2FA authentication with a TOTP provider not enabled
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a session must be opened
    And It is redirect to "/user"

  Scenario: Login user with sodium but with good credential and 2FA enabled and with wrong code
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a user with password "testtest"
    And an 2FA authentication with a TOTP provider enabled
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And It is redirect to "/user/home"
    When the client follows the redirection
    Then The client must accept a response
    And It is redirect to "/user/2fa"
    When Symfony will receive a wrong 2FA Code
    Then The client must accept a response
    And a session must be opened
    And It is redirect to "/user/2fa"

  Scenario: Login user with sodium but with good credential and 2FA enabled and with valid code
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a user with password "testtest"
    And an 2FA authentication with a TOTP provider enabled
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And It is redirect to "/user/home"
    When the client follows the redirection
    Then The client must accept a response
    And It is redirect to "/user/2fa"
    When Symfony will receive a valid 2FA Code
    Then The client must accept a response
    And a session must be opened
    And It is redirect to "/user"

  Scenario: Login user with sodium but with good credential and enabling 2FA
    Given I have DI With Symfony initialized
    And a twig templating engine
    And a user with password "testtest"
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a session must be opened
    And It is redirect to "/user"
    When Symfony will receive a request to enable 2FA
    Then The client must accept a response
    And the user have a disabled TOTPAuth configuration
    When Symfony will receive a valid 2FA Confirmation
    Then The client must accept a response
    And It is redirect to "/user/common/2fa/enable"
    When the client follows the redirection
    Then The client must accept a response
    And the user have an enabled TOTPAuth configuration