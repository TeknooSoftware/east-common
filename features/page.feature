Feature: Rendering and return an HTML dynamic page stored into a MongoDB server via an HTTP response
  As a developer, I need to render and serve, via a server following the #East programming philosophy, dynamic
  contents, stored into a MongoDB server or cluster. And serve it via a HTTP response.
  A dynamic page can host several contents, in different blocks, according to the type of the page

  Scenario: Render and Serve a hosted image
    Given I have DI initialized
    And I register a router
    And a Content Loader
    And a Endpoint able to render and serve page.
    And a type of page, called "type1" with "2" blocks "block1,block2" and template "Acme:MyBundle:type1.html.twig" with "block1:{block1} block2:{block2}"
    And a templating engine
    And an available page with the slug "foo-bar" of type "type1"
    And I register a router
    And The router can process the request "#/page/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "contentEndPoint"
    When The server will receive the request "https://foo.com/page/foo-bar"
    Then The client must accept a response
    And I should get "block1:hello block2:world"

  Scenario: Return 404 page when the content is not available
    Given I have DI initialized
    And I register a router
    And a Content Loader
    And a Endpoint able to render and serve page.
    And a type of page, called "type1" with "2" blocks "block1,block2" and template "Acme:MyBundle:type1.html.twig" with "block1{block1} block2{block2}"
    And a templating engine
    And an available page with the slug "foo-bar" of type "type1"
    And I register a router
    And The router can process the request "#/page/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "contentEndPoint"
    When The server will receive the request "https://foo.com/page/bar-foo"
    Then The client must accept a response
    And I should get "Error 404"

  Scenario: Return error when the content has an error
    Given I have DI initialized
    And I register a router
    And a Content Loader
    And a Endpoint able to render and serve page.
    And a type of page, called "type1" with "2" blocks "block1,block2" and template "Acme:MyBundle:type1.html.twig" with "block1{block1} block2{block2}"
    And a templating engine
    And an available page with the slug "page-with-error" of type "type1"
    And I register a router
    And The router can process the request "#/page/(?P<slug>[a-zA-Z0-9\.]+)#is" to controller "contentEndPoint"
    When The server will receive the request "https://foo.com/page/page-with-error"
    Then The client must accept an error