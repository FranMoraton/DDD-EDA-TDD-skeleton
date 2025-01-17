Feature: Register Event command
#
#    Scenario: Register Event command
#        Given the buses are clean
#        When the following event:
#        """
#        {
#            "message_id": "e08f603c-8566-4e20-accd-ab4b640431b2",
#            "type": "company.marketplace.1.domain_event.event.was_created",
#            "payload": {
#            },
#            "aggregate_id": "f48afcd3-2434-4134-b3c5-ba8fcc35af32",
#            "occurred_on": "2025-01-17T15:21:44+00:00"
#        }
#        """
#        Then the "company.marketplace.1.command.event.register" command should be dispatched