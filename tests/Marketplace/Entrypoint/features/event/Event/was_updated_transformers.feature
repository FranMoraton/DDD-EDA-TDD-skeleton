Feature: Event Was Updated transformers

    Scenario: Register Event command
        Given the buses are clean
        When the following event:
        """
        {
            "message_id": "6b13c149-d6cf-466b-b9e8-93ec404963a2",
            "type": "company.marketplace.1.domain_event.event.was_updated",
            "payload": {
                "base_event_id": 291,
                "sell_mode": "online",
                "title": "Camela en concierto",
                "event_start_date": "2021-06-30T21:00:00+00:00",
                "event_end_date": "2021-06-30T22:00:00+00:00",
                "event_id": 291,
                "sell_from": "2020-07-01T00:00:00+00:00",
                "sell_to": "2021-06-30T20:00:00+00:00",
                "sold_out": true,
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
                ],
                "request_time": "2025-01-17T20:54:09+00:00",
                "organizer_company_id": null,
                "min_price": 55,
                "max_price": 100
            },
            "aggregate_id": "1f4cbde7-5d5c-4be3-870e-1292d5728edf",
            "occurred_on": "2025-01-17T20:54:09+00:00"
        }
        """
        Then the "company.marketplace.1.command.event_projection.upsert" command should be dispatched