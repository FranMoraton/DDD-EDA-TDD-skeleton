Feature: Bring Events from provider

    Scenario: Bring Events from provider
        Given the buses are clean
        And the environment is clean
        And user is authenticated
        When I send a "POST" request to "/marketplace/v1/events/provider" with body:
        """
        {
        }
        """
        And the response status should be 200
        And the "company.marketplace.1.command.event.bring_from_provider" command should be dispatched
