Feature: Create an API key with the key factory, without a form and without a signed in user
  As a developer, I need to create an API key for a user from a console command or from an API, without a signed in
  user and without a form, with the key factory (`ApiKeyFactoryInterface`). The factory checks the name and the scope of
  the key, gives its secret only once to its caller, and keeps with the key the SHA-256 digest and the hash of this
  secret. When the application lists scopes (`teknoo.east.common.bundle.api_keys.scopes`), each key must have one scope
  of this list. Without this list, a key has no scope, as before.

  Background:
    Given I have DI With Symfony initialized
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"

  Scenario: Create an API key without a list of scopes
    When the key factory creates the API key "cli-key", expiring at "2099-12-31 23:59:59"
    Then the key factory gives the value of the API key "cli-key", remembered as "api key secret"
    And the API key "cli-key" of the user was created at "2050-06-01 12:00:00" and expires at "2099-12-31 23:59:59"
    And the API key "cli-key" of the user has no scope
    And the digest of the secret "<api key secret>" is stored with the API key "cli-key" of the user
    And only the hash of the secret "<api key secret>" is stored with the API key "cli-key" of the user

  Scenario: Refuse a scope when the application lists no scope
    When the key factory creates the API key "cli-key" with the scope "inference", expiring at "2099-12-31 23:59:59"
    Then the key factory refuses the API key with the error "teknoo.east.common.api_keys.error.invalid_scope"
    And the user has no API key "cli-key"

  Scenario: Create an API key with a scope of the list of the application
    When the key factory with scopes creates the API key "cli-key" with the scope "inference", expiring at "2099-12-31 23:59:59"
    Then the key factory gives the value of the API key "cli-key", remembered as "api key secret"
    And the API key "cli-key" of the user was created at "2050-06-01 12:00:00" and expires at "2099-12-31 23:59:59"
    And the API key "cli-key" of the user has the scope "inference"
    And the digest of the secret "<api key secret>" is stored with the API key "cli-key" of the user
    And only the hash of the secret "<api key secret>" is stored with the API key "cli-key" of the user

  Scenario: Refuse an API key without scope when the application lists scopes
    When the key factory with scopes creates the API key "cli-key", expiring at "2099-12-31 23:59:59"
    Then the key factory refuses the API key with the error "teknoo.east.common.api_keys.error.invalid_scope"
    And the user has no API key "cli-key"

  Scenario: Refuse a scope outside the list of the application
    When the key factory with scopes creates the API key "cli-key" with the scope "admin", expiring at "2099-12-31 23:59:59"
    Then the key factory refuses the API key with the error "teknoo.east.common.api_keys.error.invalid_scope"
    And the user has no API key "cli-key"

  Scenario: Refuse an invalid name
    When the key factory creates the API key "My key", expiring at "2099-12-31 23:59:59"
    Then the key factory refuses the API key with the error "teknoo.east.common.api_keys.form.name.regex_error"
    And the user has no API key "My key"

  Scenario: Refuse a name already used by another key of the user
    Given an API key "cli-key" for the user with:
      | secret    | ak_old              |
      | expired   | false               |
      | createdAt | 2050-01-01 00:00:00 |
      | expiresAt | 2099-12-31 00:00:00 |
    When the key factory creates the API key "cli-key", expiring at "2099-12-31 23:59:59"
    Then the key factory refuses the API key with the error "teknoo.east.common.api_keys.error.already_exists"
    And the API key "cli-key" of the user was created at "2050-01-01 00:00:00" and expires at "2099-12-31 00:00:00"
