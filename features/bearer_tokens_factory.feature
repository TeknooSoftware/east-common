Feature: Create a bearer token with the bearer token factory, without a form and without a signed in user
  As a developer, I need to create a bearer token for a user, an API key that an API client sends with each request,
  from a console command or from an API, without a signed in user and without a form, with the bearer token factory
  (`BearerTokenFactoryInterface`). The factory checks the name of the key, gives its secret only once to its caller,
  and keeps with the key only the SHA-256 digest of this secret.

  Background:
    Given I have DI With Symfony initialized
    And a user with password "testtest"
    And set current datetime to "2050-06-01 12:00:00"

  Scenario: Create a bearer token
    When the bearer token factory creates the bearer token "cli-key", expiring at "2099-12-31 23:59:59"
    Then the bearer token factory gives the value of the bearer token "cli-key", remembered as "bearer secret"
    And the bearer token "cli-key" of the user was created at "2050-06-01 12:00:00" and expires at "2099-12-31 23:59:59"
    And only the digest of the secret "<bearer secret>" is stored with the bearer token "cli-key" of the user

  Scenario: Refuse an invalid name
    When the bearer token factory creates the bearer token "My key", expiring at "2099-12-31 23:59:59"
    Then the bearer token factory refuses the bearer token with the error "teknoo.east.common.bearer_tokens.error.invalid_name"
    And the user has no bearer token "My key"

  Scenario: Refuse a name already used by another bearer token of the user
    Given a bearer token "cli-key" for the user with:
      | secret    | bt_old              |
      | expired   | false               |
      | createdAt | 2050-01-01 00:00:00 |
      | expiresAt | 2099-12-31 00:00:00 |
    When the bearer token factory creates the bearer token "cli-key", expiring at "2099-12-31 23:59:59"
    Then the bearer token factory refuses the bearer token with the error "teknoo.east.common.bearer_tokens.error.already_exists"
    And the bearer token "cli-key" of the user was created at "2050-01-01 00:00:00" and expires at "2099-12-31 00:00:00"
