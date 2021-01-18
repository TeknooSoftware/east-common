Feature: Return a required media (an image by example) stored into a database server via an HTTP response
  As a developer, I need to serve, via a server following the #East programming philosophy, media, included images,
  stored into a database server or cluster.

  Scenario: Serve a hosted image
    Given I have DI initialized
    And I register a router
    And a Media Loader
    And an available image called "image.jpeg"
    And a templating engine
    And a Endpoint able to serve resource from database.
    And The router can process the request "#/media/(?P<id>[a-zA-Z0-9\.]+)#is" to controller "mediaEndPoint"
    When The server will receive the request "https://foo.com/media/image.jpeg"
    Then The client must accept a response
    And I should get "fooBar"

  Scenario: Return 404 when the image is not available
    Given I have DI initialized
    And I register a router
    And a Media Loader
    And a templating engine
    And a Endpoint able to serve resource from database.
    And The router can process the request "#/media/(?P<id>[a-zA-Z0-9\.]+)#is" to controller "mediaEndPoint"
    When The server will receive the request "https://foo.com/media/image.jpeg"
    Then The client must accept an error