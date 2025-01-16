Feature: Update User

    Scenario: Update correct User
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
        When I send a "PATCH" request to "/users/v1/users/e56bf1de-2838-4755-8dd1-08d754e9f232" with body:
        """
        {
            "email": "test1111@gmail.com",
            "role": "ROLE_ADMIN",
            "password": "Test1234$"
        }
        """
        And the response status should be 200

    Scenario: Update User Does not exist
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
        When I send a "PATCH" request to "/users/v1/users/539f14a6-e9c7-4dd0-8a53-378edce9c5ba" with body:
        """
        {
            "email": "test2222@gmail.com",
            "role": "ROLE_ADMIN",
            "password": "Test1234$"
        }
        """
        And the response status should be 404

    Scenario: Update User with email in use
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
                "id": "539f14a6-e9c7-4dd0-8a53-378edce9c5ba",
                "email": "test2222@gmail.com",
                "role": "ROLE_ADMIN",
                "password": "Test1234$"
            },
            {
                "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                "email": "test1111@gmail.com",
                "role": "ROLE_ADMIN",
                "password": "Test1234$"
            }
        ]
        """
        When I send a "PATCH" request to "/users/v1/users/539f14a6-e9c7-4dd0-8a53-378edce9c5ba" with body:
        """
        {
            "email": "test1111@gmail.com",
            "role": "ROLE_ADMIN",
            "password": "Test1234$"
        }
        """
        And the response status should be 409
