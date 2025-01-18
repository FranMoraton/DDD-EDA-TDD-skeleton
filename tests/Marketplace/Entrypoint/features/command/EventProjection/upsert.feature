Feature: Upsert Event projection command

    Scenario: Upsert Event projection command
        Given the buses are clean
        And these EventProjections exist
        """
        [
            {
                "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                "title": "Camela en concierto",
                "start_date": "2021-06-30",
                "start_time": "21:00:00",
                "end_date": "2021-06-30",
                "end_time": "22:00:00",
                "min_price": 0,
                "max_price": 30,
                "starts_at": "2021-06-30T21:00:00+00:00",
                "ends_at": "2021-06-30T22:00:00+00:00",
                "last_event_date": "2025-01-17T17:40:16+00:00"
            }
        ]
        """
        When the following command:
        """
        {
            "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
            "type": "company.marketplace.1.command.event_projection.upsert",
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
                "last_event_date": "2025-01-17T17:41:16+00:00",
                "min_price": 50,
                "max_price": 100,
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
