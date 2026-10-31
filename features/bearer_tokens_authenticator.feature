Feature: Authenticate each request of an API client with a bearer token, without a JWT token
  As a developer, I need to authenticate each request of an API client with a bearer token of a user, sent in the
  header `Authorization: Bearer` or in a second header of the firewall (here `x-api-key`), with the stateless
  authenticator of East Common (firewall key `bearer_token`). The key is found from the SHA-256 digest of its value,
  without a slow hash and without cache: a removed key fails at the next request. The key must not be expired and its
  user must be active. The user does not need a password. Failures are rendered by the failure handler of the firewall,
  and a failure of the database is not a wrong key.

  Background:
    Given I have DI With Symfony initialized for API
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"
    And the bearer token factory creates the bearer token "inference-key", expiring at "2099-12-31 23:59:59"
    And the bearer token factory gives the value of the bearer token "inference-key", remembered as "bearer secret"

  Scenario: Authenticate a request with a bearer token in the header Authorization
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software",
          "bearerTokenName": "inference-key"
        }
      }
      """

  Scenario: Authenticate a request with a bearer token in the second header of the firewall
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "x-api-key" with the value "<bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software",
          "bearerTokenName": "inference-key"
        }
      }
      """

  Scenario: Authenticate a request with the same bearer token in the 2 headers
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends the header "x-api-key" with the value "<bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response status code is 200

  Scenario: Refuse a request with 2 different values in the 2 headers
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends the header "x-api-key" with the value "bt_other" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.different_values"

  Scenario: Refuse a request without bearer token
    When the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.missing"

  Scenario: Refuse an unknown bearer token
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer bt_0123456789abcdef" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.invalid"

  Scenario: Tell a failure of the database from a wrong bearer token
    Given the user is found from the digest of the secret "<bearer secret>"
    And the database is not available
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 503 with the message "Authentication request could not be processed due to a system problem."

  Scenario: Refuse a bearer token after its expiration date
    Given the bearer token factory creates the bearer token "old-key", expiring at "2050-06-01 11:59:59"
    And the bearer token factory gives the value of the bearer token "old-key", remembered as "old secret"
    And the user is found from the digest of the secret "<old secret>"
    When the API client sends the header "Authorization" with the value "Bearer <old secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.invalid"

  Scenario: Refuse a bearer token marked as expired
    Given the bearer token "inference-key" of the user is marked as expired
    And the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.invalid"

  Scenario: Refuse a removed bearer token from the next request
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response status code is 200
    When the bearer token "inference-key" of the user is removed
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.invalid"

  Scenario: Refuse the bearer token of a disabled user
    Given the user is disabled
    And the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "Authorization" with the value "Bearer <bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.invalid"

  Scenario: Authenticate the bearer token of a user without password
    Given a user without password
    And the bearer token factory creates the bearer token "service-key", expiring at "2099-12-31 23:59:59"
    And the bearer token factory gives the value of the bearer token "service-key", remembered as "service secret"
    And the user is found from the digest of the secret "<service secret>"
    When the API client sends the header "Authorization" with the value "Bearer <service secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "service@teknoo.software",
          "bearerTokenName": "service-key"
        }
      }
      """

  Scenario: Refuse an API key of the JWT flow with the bearer token authenticator
    Given an API key "behat-key" for the user with:
      | secret    | bt_0123456789abcdef0123456789abcdef |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |
    And the user is found from the digest of the secret "bt_0123456789abcdef0123456789abcdef"
    When the API client sends the header "Authorization" with the value "Bearer bt_0123456789abcdef0123456789abcdef" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.invalid"

  Scenario: Ignore another header on a firewall without second header
    Given the user is found from the digest of the secret "<bearer secret>"
    When the API client sends the header "x-api-key" with the value "<bearer secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/bearer-token-one-header/me"
    Then the API response is the error 401 with the message "teknoo.east.common.bearer_tokens.error.missing"
