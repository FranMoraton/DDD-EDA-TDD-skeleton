Feature: Create User

    Scenario: Create correct User
        Given the environment is clean
        And user is authenticated with roles:
        """
        [
            "ROLE_ADMIN"
        ]
        """
        When I send a "POST" request to "/users/v1/users" with body:
        """
        {
            "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
            "email": "test2222@gmail.com",
            "role": "ROLE_ADMIN",
            "password": "Test1234$"
        }
        """
        And the response status should be 200

    Scenario: Create Already Exist User
        Given the environment is clean
        And user is authenticated with roles:
        """
        [
            "ROLE_ADMIN"
        ]
        """
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
        When I send a "POST" request to "/users/v1/users" with body:
        """
        {
            "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
            "email": "test2222@gmail.com",
            "role": "ROLE_ADMIN",
            "password": "Test1234$"
        }
        """
        And the response status should be 409
