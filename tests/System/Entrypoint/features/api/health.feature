Feature: Public website
    Scenario: Call to health endpoint
        When I send a "GET" request to "/"
        Then the response status should be "200"