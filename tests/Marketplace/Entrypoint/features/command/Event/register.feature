Feature: Register Event command

    Scenario: Register Event command
        Given the buses are clean
        When the following command:
        """
        {
            "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
            "type": "company.marketplace.1.command.event.register",
            "payload": {
                "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                "base_event_id": "291",
                "sell_mode": "online",
                "organizer_company_id": "2",
                "title": "Camela en concierto",
                "event_start_date": "2021-06-30T21:00:00",
                "event_end_date": "2021-06-30T22:00:00",
                "event_id": "291",
                "sell_from": "2020-07-01T00:00:00",
                "sell_to": "2021-06-30T20:00:00",
                "sold_out": "false",
                "request_time": "2025-01-17T17:41:16+00:00",
                "zones": [
                    {
                        "zone_id": "40",
                        "capacity": "243",
                        "price": "20.00",
                        "name": "Platea",
                        "numbered": "true"
                    },
                    {
                        "zone_id": "38",
                        "capacity": "100",
                        "price": "15.00",
                        "name": "Grada 2",
                        "numbered": "false"
                    },
                    {
                        "zone_id": "30",
                        "capacity": "90",
                        "price": "30.00",
                        "name": "A28",
                        "numbered": "true"
                    }
                ]
            }
        }
        """
        Then the "company.marketplace.1.domain_event.event.was_created" event should be dispatched

    Scenario: Register Event command without organizer_company null coalesce
        Given the buses are clean
        When the following command:
        """
        {
            "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
            "type": "company.marketplace.1.command.event.register",
            "payload": {
                "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                "base_event_id": "291",
                "sell_mode": "online",
                "title": "Camela en concierto",
                "event_start_date": "2021-06-30T21:00:00",
                "event_end_date": "2021-06-30T22:00:00",
                "event_id": "291",
                "sell_from": "2020-07-01T00:00:00",
                "sell_to": "2021-06-30T20:00:00",
                "sold_out": "false",
                "request_time": "2025-01-17T17:41:16+00:00",
                "zones": [
                    {
                        "zone_id": "40",
                        "capacity": "243",
                        "price": "20.00",
                        "name": "Platea",
                        "numbered": "true"
                    },
                    {
                        "zone_id": "38",
                        "capacity": "100",
                        "price": "15.00",
                        "name": "Grada 2",
                        "numbered": "false"
                    },
                    {
                        "zone_id": "30",
                        "capacity": "90",
                        "price": "30.00",
                        "name": "A28",
                        "numbered": "true"
                    }
                ]
            }
        }
        """
        Then the "company.marketplace.1.domain_event.event.was_created" event should be dispatched

    Scenario: Register Event command without organizer_company null coalesce
        Given the buses are clean
        And these Events exist
        """
        [
            {
                "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                "base_event_id": 291,
                "sell_mode": "online",
                "title": "Camela en concierto",
                "event_start_date": "2021-06-30T21:00:00+00:00",
                "event_end_date": "2021-06-30T22:00:00+00:00",
                "event_id": 291,
                "sell_from": "2020-07-01T00:00:00+00:00",
                "sell_to": "2021-06-30T20:00:00+00:00",
                "sold_out": false,
                "request_time": "2025-01-15T17:41:16+00:00",
                "organizer_company_id": "2",
                "zones": [
                    {
                        "zone_id": 40,
                        "capacity": 243,
                        "price": 20,
                        "name": "Platea",
                        "numbered": true
                    },
                    {
                        "zone_id": 38,
                        "capacity": 100,
                        "price": 15,
                        "name": "Grada 2",
                        "numbered": false
                    },
                    {
                        "zone_id": 30,
                        "capacity": 90,
                        "price": 30,
                        "name": "A28",
                        "numbered": true
                    }
                ]
            }
        ]
        """
        When the following command:
        """
        {
            "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
            "type": "company.marketplace.1.command.event.register",
            "payload": {
                "id": "c3f93a40-744f-4eb9-a7a9-dee850ab2c2e",
                "base_event_id": "291",
                "sell_mode": "online",
                "title": "Camela en concierto",
                "event_start_date": "2021-06-30T21:00:00",
                "event_end_date": "2021-06-30T22:00:00",
                "event_id": "291",
                "sell_from": "2020-07-01T00:00:00",
                "sell_to": "2021-06-30T20:00:00",
                "sold_out": "false",
                "request_time": "2025-01-17T17:41:16+00:00",
                "zones": [
                    {
                        "zone_id": "40",
                        "capacity": "243",
                        "price": "20.00",
                        "name": "Platea",
                        "numbered": "true"
                    },
                    {
                        "zone_id": "38",
                        "capacity": "100",
                        "price": "15.00",
                        "name": "Grada 2",
                        "numbered": "false"
                    },
                    {
                        "zone_id": "30",
                        "capacity": "90",
                        "price": "30.00",
                        "name": "A28",
                        "numbered": "true"
                    }
                ]
            }
        }
        """
        Then the "company.marketplace.1.domain_event.event.was_updated" event should be dispatched