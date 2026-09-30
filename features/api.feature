Feature: Expose objects via a JSON API, with the Twig extensions and the templates shipped by East Common
  As a developer, I need to expose persisted objects via a JSON API, with the same endpoints as HTML pages (only
  parameters of routes change). Bodies can be sent as JSON (partial update) or urlencoded. Responses use the envelope
  {"meta": {...}, "data": ...}, and errors are rendered with the template shipped by the bundle.

  Background:
    Given I have DI With Symfony initialized for API

  Scenario: Create an object from a JSON request and get it as JSON
    When the API client sends a "POST" JSON request to "https://foo.com/api/v1/my_object/new" with:
      """
      {
        "name": "foo",
        "slug": "bar"
      }
      """
    Then It is redirect to "/api/v1/my_object/[a-zA-Z0-9]+"
    And An object must be persisted
    When the client follows the redirection
    Then the API response status code is 200
    And the API response is a JSON response
    And the API response contains:
      """
      {
        "meta": {
          "@class": "Teknoo\\Tests\\East\\Common\\Behat\\Object\\MyObject"
        },
        "data": {
          "@class": "Teknoo\\Tests\\East\\Common\\Behat\\Object\\MyObject",
          "name": "foo",
          "slug": "bar",
          "saved": "foo",
          "publishedAt": null
        }
      }
      """

  Scenario: Create an object from an urlencoded request
    When the API client sends a "POST" form request to "https://foo.com/api/v1/my_object/new" with "my_object%5Bname%5D=foo&my_object%5Bslug%5D=bar"
    Then It is redirect to "/api/v1/my_object/[a-zA-Z0-9]+"
    And An object must be persisted
    When the client follows the redirection
    Then the API response status code is 200
    And the API response contains:
      """
      {
        "data": {
          "name": "foo",
          "slug": "bar"
        }
      }
      """

  Scenario: Create and publish an object from a JSON request
    When the API client sends a "POST" JSON request to "https://foo.com/api/v1/my_object/new" with:
      """
      {
        "name": "foo",
        "slug": "bar",
        "publish": true
      }
      """
    Then It is redirect to "/api/v1/my_object/[a-zA-Z0-9]+"
    And An object must be persisted
    And the created object must be published

  Scenario: Get an object
    Given an object "foo" named "Foo" with the slug "foo-slug"
    When the API client sends a "GET" request to "https://foo.com/api/v1/my_object/foo"
    Then the API response status code is 200
    And the API response is a JSON response
    And the API response is:
      """
      {
        "meta": {
          "id": "foo",
          "@class": "Teknoo\\Tests\\East\\Common\\Behat\\Object\\MyObject"
        },
        "data": {
          "@class": "Teknoo\\Tests\\East\\Common\\Behat\\Object\\MyObject",
          "id": "foo",
          "name": "Foo",
          "slug": "foo-slug",
          "saved": null,
          "publishedAt": null
        }
      }
      """

  Scenario: Update partially an object from a JSON request
    Given an object "foo" named "Foo" with the slug "foo-slug"
    When the API client sends a "PUT" JSON request to "https://foo.com/api/v1/my_object/foo" with:
      """
      {
        "name": "Bar"
      }
      """
    Then the API response status code is 200
    And An object "foo" must be updated
    And the API response contains:
      """
      {
        "meta": {
          "id": "foo"
        },
        "data": {
          "id": "foo",
          "name": "Bar",
          "slug": "foo-slug",
          "saved": "foo"
        }
      }
      """

  Scenario: Get errors of an invalid form, in a JSON response
    Given an object "foo" named "Foo" with the slug "foo-slug"
    When the API client sends a "PUT" JSON request to "https://foo.com/api/v1/my_object/foo" with:
      """
      {
        "name": "Bar",
        "unknown": "field"
      }
      """
    Then the API response status code is 400
    And the API response is a JSON response
    And the API response contains errors on the fields "."
    And the API response is:
      """
      {
        "meta": {
          "errors": true
        },
        "data": {
          ".": "This form should not contain extra fields."
        }
      }
      """

  Scenario: Send a malformed JSON body
    When the API client sends a "POST" JSON request to "https://foo.com/api/v1/my_object/new" with:
      """
      {"name": "foo
      """
    Then the API response is the error 400 with the message "Malformed JSON body"

  Scenario: Get an unknown object, the error is rendered with the template shipped by the bundle
    When the API client sends a "GET" request to "https://foo.com/api/v1/my_object/unknown"
    Then the API response is the error 404

  Scenario: List objects with a pagination
    Given an object "foo" named "Foo" with the slug "foo-slug"
    And an object "bar" named "Bar" with the slug "bar-slug"
    When the API client sends a "GET" request to "https://foo.com/api/v1/my_objects"
    Then the API response status code is 200
    And the API response is a JSON response
    And the API response is the page 1 of 1 with 2 elements
    And the API response contains:
      """
      {
        "data": [
          {
            "id": "foo",
            "name": "Foo",
            "slug": "foo-slug"
          },
          {
            "id": "bar",
            "name": "Bar",
            "slug": "bar-slug"
          }
        ]
      }
      """
    And the API response does not contain the key "saved" in "data.0"

  Scenario: Delete an object from a JSON API
    Given an object "foo" named "Foo" with the slug "foo-slug"
    When the API client sends a "DELETE" request to "https://foo.com/api/v1/my_object/foo/delete"
    Then the API response status code is 200
    And the API response is:
      """
      {
        "meta": {
          "id": "foo",
          "@class": "Teknoo\\Tests\\East\\Common\\Behat\\Object\\MyObject",
          "deleted": "success"
        },
        "data": {
          "@class": "Teknoo\\Tests\\East\\Common\\Behat\\Object\\MyObject",
          "id": "foo",
          "name": "Foo"
        }
      }
      """
    And the last object updated must be deleted

  Scenario: Get a user, with the template shipped by the bundle, without its authentication data
    Given a user "u1" named "Max" "Doe" with the email "max@teknoo.software"
    When the API client sends a "GET" request to "https://foo.com/api/v1/user/u1"
    Then the API response status code is 200
    And the API response is:
      """
      {
        "meta": {
          "id": "u1",
          "@class": "Teknoo\\East\\Common\\Object\\User"
        },
        "data": {
          "@class": "Teknoo\\East\\Common\\Object\\User",
          "id": "u1",
          "firstName": "Max",
          "lastName": "Doe",
          "email": "max@teknoo.software",
          "roles": ["ROLE_USER"],
          "active": true
        }
      }
      """

  Scenario: List users, with the template shipped by the bundle
    Given a user "u1" named "Max" "Doe" with the email "max@teknoo.software"
    When the API client sends a "GET" request to "https://foo.com/api/v1/users"
    Then the API response status code is 200
    And the API response is the page 1 of 1 with 1 elements
    And the API response contains:
      """
      {
        "data": [
          {
            "id": "u1",
            "firstName": "Max",
            "lastName": "Doe",
            "email": "max@teknoo.software"
          }
        ]
      }
      """
    And the API response does not contain the key "roles" in "data.0"

  Scenario: Delete a user, with the template shipped by the bundle
    Given a user "u1" named "Max" "Doe" with the email "max@teknoo.software"
    When the API client sends a "DELETE" request to "https://foo.com/api/v1/user/u1/delete"
    Then the API response status code is 200
    And the API response is:
      """
      {
        "meta": {
          "id": "u1",
          "@class": "Teknoo\\East\\Common\\Object\\User",
          "deleted": "success"
        },
        "data": {
          "@class": "Teknoo\\East\\Common\\Object\\User",
          "id": "u1",
          "email": "max@teknoo.software"
        }
      }
      """
    And the last object updated must be deleted

  Scenario: Get a media, with the template shipped by the bundle, without its local path
    Given a media "m1" named "logo"
    When the API client sends a "GET" request to "https://foo.com/api/v1/media/m1"
    Then the API response status code is 200
    And the API response is:
      """
      {
        "meta": {
          "id": "m1",
          "@class": "Teknoo\\East\\Common\\Object\\Media"
        },
        "data": {
          "@class": "Teknoo\\East\\Common\\Object\\Media",
          "id": "m1",
          "name": "logo",
          "length": 123,
          "metadata": {
            "contentType": "image/png",
            "fileName": "logo.png",
            "alternative": "Alt logo"
          }
        }
      }
      """

  Scenario: List media, with the template shipped by the bundle
    Given a media "m1" named "logo"
    And a media "m2" named "banner"
    When the API client sends a "GET" request to "https://foo.com/api/v1/media"
    Then the API response status code is 200
    And the API response is the page 1 of 1 with 2 elements
    And the API response contains:
      """
      {
        "data": [
          {
            "id": "m1",
            "name": "logo",
            "metadata": {
              "fileName": "logo.png"
            }
          },
          {
            "id": "m2",
            "name": "banner"
          }
        ]
      }
      """

  Scenario: Delete a media, with the template shipped by the bundle
    Given a media "m1" named "logo"
    When the API client sends a "DELETE" request to "https://foo.com/api/v1/media/m1/delete"
    Then the API response status code is 200
    And the API response is:
      """
      {
        "meta": {
          "id": "m1",
          "@class": "Teknoo\\East\\Common\\Object\\Media",
          "deleted": "success"
        },
        "data": {
          "@class": "Teknoo\\East\\Common\\Object\\Media",
          "id": "m1",
          "name": "logo"
        }
      }
      """
