Feature: Register Event command

    Scenario: Register Event command
        Given the buses are clean
        When the following command:
        """
        {
            "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
            "type": "company.marketplace.1.command.event.register",
            "payload": {
                "id": "f48afcd3-2434-4134-b3c5-ba8fcc35af32"
            }
        }
        """
        Then the "company.marketplace.1.domain_event.event.was_created" event should be dispatched