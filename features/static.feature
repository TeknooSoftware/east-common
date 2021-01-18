Feature: Rendering and return a rendered static content via an HTTP response
  As a developer, I need to render, via a server following the #East programming philosophy, a static content,
  defined in the configuration. And serve it via a HTTP response.

  Scenario: Serve a static content image
    Given I have DI initialized
    And I register a router
    And a templating engine
    And a Endpoint able to render and serve this template.
    And a template "Acme:MyBundle:template.html.twig" with "fooBar"
    And The router can process the request "#/static/foo#" to controller "staticEndPoint"
    When The server will receive the request "https://foo.com/static/foo"
    Then The client must accept a response
    And I should get "fooBar"
