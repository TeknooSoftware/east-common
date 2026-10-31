Feature: Keep the JWT flow with the API keys created before and after East Common 4.9
  As a developer, I need to keep the log in with an API key to get a JWT token (`json_login`) for the keys created
  before East Common 4.9 and for the keys created by the key factory, which keep the hash of their value.

  Background:
    Given I have DI With Symfony initialized for API
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"

  Scenario: Log in with an API key created before East Common 4.9 to get a JWT token
    Given an API key "behat-key" for the user with:
      | secret    | ak_0123456789abcdef0123456789abcdef |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "behat-key:admin@teknoo.software",
        "token": "ak_0123456789abcdef0123456789abcdef"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"

  Scenario: Log in with an API key created by the key factory to get a JWT token
    Given the key factory creates the API key "cli-key", expiring at "2099-12-31 23:59:59"
    And the key factory gives the value of the API key "cli-key", remembered as "cli secret"
    When the API client sends a "POST" JSON request to "https://foo.com/secured-api/v1/login" with:
      """
      {
        "username": "cli-key:admin@teknoo.software",
        "token": "<cli secret>"
      }
      """
    Then the API response status code is 200
    And the value "data.token" of the API response is remembered as "jwt"
    And the JWT token "<jwt>" is issued to "admin@teknoo.software" and expires at "2050-06-02 12:00:00"
