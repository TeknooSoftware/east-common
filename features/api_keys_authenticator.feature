Feature: Authenticate each request of an API client with an API key, without a JWT token
  As a developer, I need to authenticate each request of an API client with an API key of a user, sent in the header
  `Authorization: Bearer` or in a second header of the firewall (here `x-api-key`), with the stateless authenticator
  of East Common (firewall key `api_key`). The key is found from the SHA-256 digest of its value, without a slow hash
  and without cache: a removed key fails at the next request. The key must not be expired, its user must be active,
  and its scope must be accepted by the firewall when the firewall lists accepted scopes. The user does not need a
  password. Failures are rendered by the failure handler of the firewall.

  Background:
    Given I have DI With Symfony initialized for API
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"
    And the key factory with scopes creates the API key "inference-key" with the scope "inference", expiring at "2099-12-31 23:59:59"
    And the key factory gives the value of the API key "inference-key", remembered as "inference secret"

  Scenario: Authenticate a request with an API key in the header Authorization
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer <inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software",
          "apiKeyName": "inference-key",
          "apiKeyScope": "inference"
        }
      }
      """

  Scenario: Authenticate a request with an API key in the second header of the firewall
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "x-api-key" with the value "<inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software",
          "apiKeyName": "inference-key"
        }
      }
      """

  Scenario: Authenticate a request with the same API key in the 2 headers
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer <inference secret>" in its next requests
    And the API client sends the header "x-api-key" with the value "<inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response status code is 200

  Scenario: Refuse a request with 2 different values in the 2 headers
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer <inference secret>" in its next requests
    And the API client sends the header "x-api-key" with the value "ak_other" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.different_values"

  Scenario: Refuse a request without API key
    When the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.missing"

  Scenario: Refuse an unknown API key
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer ak_0123456789abcdef" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.invalid"

  Scenario: Refuse an API key after its expiration date
    Given the key factory with scopes creates the API key "old-key" with the scope "inference", expiring at "2050-06-01 11:59:59"
    And the key factory gives the value of the API key "old-key", remembered as "old secret"
    And the user is found from the digest of the secret "<old secret>"
    When the API client sends the header "Authorization" with the value "Bearer <old secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.invalid"

  Scenario: Refuse an API key marked as expired
    Given the API key "inference-key" of the user is marked as expired
    And the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer <inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.invalid"

  Scenario: Refuse a removed API key from the next request
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer <inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response status code is 200
    When the API key "inference-key" of the user is removed
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.invalid"

  Scenario: Refuse the API key of a disabled user
    Given the user is disabled
    And the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "Authorization" with the value "Bearer <inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.invalid"

  Scenario: Authenticate the API key of a user without password
    Given a user without password
    And the key factory with scopes creates the API key "service-key" with the scope "inference", expiring at "2099-12-31 23:59:59"
    And the key factory gives the value of the API key "service-key", remembered as "service secret"
    And the user is found from the digest of the secret "<service secret>"
    When the API client sends the header "Authorization" with the value "Bearer <service secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "service@teknoo.software",
          "apiKeyName": "service-key",
          "apiKeyScope": "inference"
        }
      }
      """

  Scenario: Refuse an API key with a scope that the firewall does not accept
    Given the key factory with scopes creates the API key "management-key" with the scope "management", expiring at "2099-12-31 23:59:59"
    And the key factory gives the value of the API key "management-key", remembered as "management secret"
    And the user is found from the digest of the secret "<management secret>"
    When the API client sends the header "Authorization" with the value "Bearer <management secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.scope_refused"

  Scenario: Authenticate an API key of any scope on a firewall without accepted scopes
    Given the key factory with scopes creates the API key "management-key" with the scope "management", expiring at "2099-12-31 23:59:59"
    And the key factory gives the value of the API key "management-key", remembered as "management secret"
    And the user is found from the digest of the secret "<management secret>"
    When the API client sends the header "Authorization" with the value "Bearer <management secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key-any-scope/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software",
          "apiKeyName": "management-key",
          "apiKeyScope": "management"
        }
      }
      """

  Scenario: Ignore another header on a firewall without second header
    Given the user is found from the digest of the secret "<inference secret>"
    When the API client sends the header "x-api-key" with the value "<inference secret>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/api-key-any-scope/me"
    Then the API response is the error 401 with the message "teknoo.east.common.api_keys.error.missing"
