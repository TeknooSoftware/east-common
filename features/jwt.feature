Feature: Authenticate API clients with API keys and JWT tokens
  As a developer, I need to let API clients log in with an API key of a user (the username is `key name:email` and the
  token is the secret of the key) to get a JWT token, then call protected routes with this JWT token in the header
  `Authorization: Bearer`. A signed in user can also generate a JWT token from a web page, and an API client can get
  a new JWT token from a valid one. Authentication failures are rendered like other errors of a JSON API (`meta` and
  `data`). The lifetime of a JWT token never exceeds `teknoo.east.common.bundle.jwt.max_days_to_live` (1 day by
  default). Endpoints, forms, routes and JSON templates are shipped by East Common (`jwt_api_login_routing.yaml`,
  `jwt_api_routing.yaml`, `jwt_routing.yaml`), JWT tokens are managed by lexik/jwt-authentication-bundle.

  Background:
    Given I have DI With Symfony initialized for API
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"
    And an API key "behat-key" for the user with:
      | secret    | ak_0123456789abcdef0123456789abcdef |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |

  Scenario: Log in with an API key, get a JWT token and use it on a protected route
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the API response is a JSON response
    And the API response contains:
      """
      {
        "meta": {
          "error": false
        }
      }
      """
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software"
        }
      }
      """

  Scenario: Log in with an API key, with a JSON body but without the header Content-Type
    When the API client sends a "POST" request to "https://foo.com/secured-api/v1/login" without JSON content type, with the body:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the API response is a JSON response
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"

  Scenario: Log in with an API key of a user with 2FA enabled, without any TOTP code
    Given an 2FA authentication with a TOTP provider enabled
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software"
        }
      }
      """

  Scenario: Log in with an API key created from the web form
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a session must be opened
    When Symfony will receive the POST request "https://foo.com/user/common/api-keys" with "api_keys_auth[name]=web-key&api_keys_auth[expiresAt]=2099-12-31"
    Then The client must accept a response
    And the value "data.newApiKey.token" of the API response is remembered as "api key secret"
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "web-key:admin@teknoo.software",
        "token": "<api key secret>"
      }
      """
    Then the API response status code is 200
    And the API response is a JSON response
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software"
        }
      }
      """

  Scenario: Refuse a login with an API key deleted from the web page
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a session must be opened
    When Symfony will receive the GET request "https://foo.com/user/common/api-keys/delete/behat-key"
    Then It is redirect to "/user/common/api-keys"
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a login with a wrong secret
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_this_is_not_the_secret_of_the_key"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a login with an unknown key name
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "unknown-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a login with a username without key name
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a login with the key of an unknown user
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:nobody@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a login with a key whose expiration date is passed
    Given an API key "old-key" for the user with:
      | secret    | ak_old_0123456789abcdef0123456789ab |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2050-05-31 00:00:00                 |
      | expired   | false                               |
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "old-key:admin@teknoo.software",
        "token": "ak_old_0123456789abcdef0123456789ab"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a login with a key marked as expired
    Given an API key "revoked-key" for the user with:
      | secret    | ak_revoked_0123456789abcdef01234567 |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | true                                |
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "revoked-key:admin@teknoo.software",
        "token": "ak_revoked_0123456789abcdef01234567"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid credentials."
        }
      }
      """

  Scenario: Refuse a request without JWT token on a protected route
    When the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "JWT Token not found"
        }
      }
      """

  Scenario: Refuse a request with an invalid JWT token on a protected route
    Given the API client sends the header "Authorization" with the value "Bearer this-is-not-a-jwt-token" in its next requests
    When the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Invalid JWT Token"
        }
      }
      """

  Scenario: Refuse a request with an expired JWT token on a protected route
    # JWT tokens are validated by Lexik with the real clock : a token created "in 2020" expired on 2020-01-02
    Given set current datetime to "2020-01-01 00:00:00"
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2020-01-02 00:00:00"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "Expired JWT Token"
        }
      }
      """

  Scenario: Generate a JWT token with an expiration date from the web form and use it on a protected route
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    And a session must be opened
    When Symfony will receive the GET request "https://foo.com/user/common/jwt-token"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": false
        },
        "data": {}
      }
      """
    When Symfony will receive the POST request "https://foo.com/user/common/jwt-token" with "jwt_configuration[expirationDate]=2050-06-02"
    Then The client must accept a response
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 00:00:00"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software"
        }
      }
      """

  Scenario: Refuse an invalid expiration date in the web form
    When Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    Then The client must accept a response
    When Symfony will receive the POST request "https://foo.com/user/common/jwt-token" with "jwt_configuration[expirationDate]=not-a-date"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          ".expirationDate": "Please enter a valid date."
        }
      }
      """

  Scenario: Get a new JWT token from the API with a JSON body and use it
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/jwt/create-token" with:
      """
      {
        "expirationDate": "2050-06-02"
      }
      """
    Then the API response status code is 200
    And the API response is a JSON response
    And the value "data.token" of the API response is remembered as "new jwt"
    And the JWT token "<new jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 00:00:00"
    When the API client sends the header "Authorization" with the value "Bearer <new jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "email": "admin@teknoo.software"
        }
      }
      """

  Scenario: Get a new JWT token from the API with an urlencoded body and use it
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "POST" form request to "https://foo.com/secured-api/v1/jwt/create-token" with "jwt_configuration%5BexpirationDate%5D=2050-06-02"
    Then the API response status code is 200
    And the API response is a JSON response
    And the value "data.token" of the API response is remembered as "new jwt"
    And the JWT token "<new jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 00:00:00"
    When the API client sends the header "Authorization" with the value "Bearer <new jwt>" in its next requests
    And the API client sends a "GET" request to "https://foo.com/secured-api/v1/me"
    Then the API response status code is 200

  Scenario: The lifetime of a new JWT token is capped to the maximum allowed
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/jwt/create-token" with:
      """
      {
        "expirationDate": "2051-01-01"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "new jwt"
    And the JWT token "<new jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"

  Scenario: A new JWT token without expiration date gets the maximum lifetime
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/jwt/create-token" with:
      """
      {
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "new jwt"
    And the JWT token "<new jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"

  Scenario: Get the errors of an invalid expiration date, in a JSON response
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    When the API client sends the header "Authorization" with the value "Bearer <jwt>" in its next requests
    And the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/jwt/create-token" with:
      """
      {
        "expirationDate": "not-a-date"
      }
      """
    Then the API response status code is 400
    And the API response is a JSON response
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          ".expirationDate": "Please enter a valid date."
        }
      }
      """

  Scenario: Refuse to create a new JWT token without JWT token
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/jwt/create-token" with:
      """
      {
        "expirationDate": "2050-06-02"
      }
      """
    Then the API response status code is 401
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "code": 401,
          "message": "JWT Token not found"
        }
      }
      """
