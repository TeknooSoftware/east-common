Feature: Minify a set of assets (css or js) into a single file

  Scenario: Create a minified css file from a set of css
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with css non minified files
    When Symfony will receive the GET request "https://foo.com/build/css/main.min.css"
    Then The client must accept a response
    And the response must be a css file
    And the content must be the new minified css

  Scenario: Create a minified css file from a set of css
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with css files already minified file into an unique file
    When Symfony will receive the GET request "https://foo.com/build/css/main.min.css"
    Then The client must accept a response
    And the response must be a css file
    And the content must be the existing minified css

  Scenario: Create a minified css versioned file from a set of css
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with css non minified files
    When Symfony will receive the GET request "https://foo.com/build/css/main.2.0.0.min.css"
    Then The client must accept a response
    And the response must be a css file
    And the content must be the new minified css

  Scenario: Create a minified css versioned file from a set of css
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with css non minified files
    When Symfony will receive the GET request "https://foo.com/build/css/main.1.0.0.min.css"
    Then The client must accept a response
    And the response must be a css file
    And the content must be the old minified css

  Scenario: Create a minified js file from a set of js
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with js non minified files
    When Symfony will receive the GET request "https://foo.com/build/js/main.min.js"
    Then The client must accept a response
    And the response must be a js file
    And the content must be the new minified js

  Scenario: Create a minified js file from a set of js
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with js files already minified file into an unique file
    When Symfony will receive the GET request "https://foo.com/build/js/main.min.js"
    Then The client must accept a response
    And the response must be a js file
    And the content must be the existing minified js

  Scenario: Create a minified js versioned file from a set of js
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with js non minified files
    When Symfony will receive the GET request "https://foo.com/build/js/main.2.0.0.min.js"
    Then The client must accept a response
    And the response must be a js file
    And the content must be the new minified js

  Scenario: Create a minified js versioned file from a set of js
    Given I have DI With Symfony initialized
    And a twig templating engine
    And with css non minified files
    When Symfony will receive the GET request "https://foo.com/build/js/main.1.0.0.min.js"
    Then The client must accept a response
    And the response must be a js file
    And the content must be the old minified js
