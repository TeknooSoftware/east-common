Feature: Manage the API keys of a user from web pages
  As a developer, I need to let a signed in user create, list and revoke its API keys from web pages, with the
  endpoints, the form and the routes shipped by East Common (`api_keys_routing.yaml`). HTML templates are provided by
  applications : here, the test application renders the page as JSON, like a JSON API response (`meta` and `data`),
  with the keys of the user and, only when the form has been submitted, its errors and the new key. The secret of a key is displayed only once, when the
  key is created : only its hash is stored with the user. A key is identified by its name, unique for a user, and has
  an expiration date.

  Background:
    Given I have DI With Symfony initialized for API
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"
    And Symfony will receive the POST request "https://foo.com/user/check" with "_username=admin@teknoo.software&_password=testtest"
    And The client must accept a response
    And a session must be opened

  Scenario: Display the page of API keys of a user without key
    When Symfony will receive the GET request "https://foo.com/user/common/api-keys"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": false
        },
        "data": {
          "apiKeys": []
        }
      }
      """

  Scenario: Create an API key from the web form
    When Symfony will receive the POST request "https://foo.com/user/common/api-keys" with "api_keys_auth[name]=behat-key&api_keys_auth[expiresAt]=2099-12-31"
    Then The client must accept a response
    And An object "userid" must be updated
    And the API response contains:
      """
      {
        "meta": {
          "error": false
        },
        "data": {
          "newApiKey": {
            "name": "behat-key"
          },
          "apiKeys": [
            {
              "name": "behat-key",
              "createdAt": "2050-06-01 12:00:00",
              "expiresAt": "2099-12-31 00:00:00",
              "expired": false
            }
          ]
        }
      }
      """
    And the value "data.newApiKey.token" of the API response matches "/^ak_[0-9a-f]{64}$/"
    And the value "data.newApiKey.token" of the API response is remembered as "api key secret"
    And only the hash of the secret "<api key secret>" is stored with the API key "behat-key" of the user

  Scenario: The secret of an API key is displayed only once
    When Symfony will receive the POST request "https://foo.com/user/common/api-keys" with "api_keys_auth[name]=behat-key&api_keys_auth[expiresAt]=2099-12-31"
    Then The client must accept a response
    And the value "data.newApiKey.token" of the API response matches "/^ak_[0-9a-f]{64}$/"
    When Symfony will receive the GET request "https://foo.com/user/common/api-keys"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": false
        },
        "data": {
          "apiKeys": [
            {
              "name": "behat-key",
              "createdAt": "2050-06-01 12:00:00",
              "expiresAt": "2099-12-31 00:00:00",
              "expired": false
            }
          ]
        }
      }
      """

  Scenario: Refuse an API key with a name already used by another key of the user
    Given an API key "behat-key" for the user with:
      | secret    | ak_0123456789abcdef0123456789abcdef |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |
    When Symfony will receive the POST request "https://foo.com/user/common/api-keys" with "api_keys_auth[name]=behat-key&api_keys_auth[expiresAt]=2060-01-01"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "errors": {
            ".name": "teknoo.east.common.api_keys.error.already_exists"
          },
          "newApiKey": {
            "name": "behat-key",
            "token": ""
          },
          "apiKeys": [
            {
              "name": "behat-key",
              "createdAt": "2050-01-01 00:00:00",
              "expiresAt": "2099-12-31 00:00:00",
              "expired": false
            }
          ]
        }
      }
      """

  Scenario: Refuse an API key with an invalid name
    When Symfony will receive the POST request "https://foo.com/user/common/api-keys" with "api_keys_auth[name]=Invalid-Name&api_keys_auth[expiresAt]=2099-12-31"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": true
        },
        "data": {
          "errors": {
            ".name": "teknoo.east.common.api_keys.form.name.regex_error"
          },
          "newApiKey": {
            "name": "Invalid-Name",
            "token": ""
          },
          "apiKeys": []
        }
      }
      """

  Scenario: List the API keys of the user
    Given an API key "first-key" for the user with:
      | secret    | ak_first_0123456789abcdef0123456789 |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |
    And an API key "second-key" for the user with:
      | secret    | ak_second_0123456789abcdef012345678 |
      | createdAt | 2050-02-01 00:00:00                 |
      | expiresAt | 2050-03-01 00:00:00                 |
      | expired   | true                                |
    When Symfony will receive the GET request "https://foo.com/user/common/api-keys"
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": false
        },
        "data": {
          "apiKeys": [
            {
              "name": "first-key",
              "createdAt": "2050-01-01 00:00:00",
              "expiresAt": "2099-12-31 00:00:00",
              "expired": false
            },
            {
              "name": "second-key",
              "createdAt": "2050-02-01 00:00:00",
              "expiresAt": "2050-03-01 00:00:00",
              "expired": true
            }
          ]
        }
      }
      """

  Scenario: Delete an API key
    Given an API key "first-key" for the user with:
      | secret    | ak_first_0123456789abcdef0123456789 |
      | createdAt | 2050-01-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |
    And an API key "second-key" for the user with:
      | secret    | ak_second_0123456789abcdef012345678 |
      | createdAt | 2050-02-01 00:00:00                 |
      | expiresAt | 2099-12-31 00:00:00                 |
      | expired   | false                               |
    When Symfony will receive the GET request "https://foo.com/user/common/api-keys/delete/first-key"
    Then It is redirect to "/user/common/api-keys"
    And An object "userid" must be updated
    When the client follows the redirection
    Then The client must accept a response
    And the API response is:
      """
      {
        "meta": {
          "error": false
        },
        "data": {
          "apiKeys": [
            {
              "name": "second-key",
              "createdAt": "2050-02-01 00:00:00",
              "expiresAt": "2099-12-31 00:00:00",
              "expired": false
            }
          ]
        }
      }
      """
