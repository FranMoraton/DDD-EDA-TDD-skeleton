Feature: Create User command

    Scenario: Create User command
        Given the buses are clean
        When the following command:
        """
        {
            "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
            "type": "company.users.1.command.user.create",
            "payload": {
                "id": "f48afcd3-2434-4134-b3c5-ba8fcc35af32",
                "email": "test2222@gmail.com",
                "role": "ROLE_ADMIN",
                "password": "Test1234$"
            }
        }
        """
        Then the "company.app.1.domain_event.user.was_created" event should be dispatched