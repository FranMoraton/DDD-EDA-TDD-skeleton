Feature: Recover Event by Id

    Scenario: Recover Event by Id
        Given the environment is clean
        And user is authenticated
        And these Users exist
        """
        [
            {
                "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                "email": "test2222@gmail.com",
                "role": "ROLE_ADMIN",
                "password": "Test1234$"
            }
        ]
        """
        When I send a "GET" request to "/marketplace/v1/events/e56bf1de-2838-4755-8dd1-08d754e9f232"
        And the response status should be 200
