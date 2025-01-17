Feature: Recover Event by Id

    Scenario: Recover Event by Id
        Given the environment is clean
        And user is authenticated
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
                "request_time": "2025-01-17T17:41:16+00:00",
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
        When I send a "GET" request to "/marketplace/v1/events/e56bf1de-2838-4755-8dd1-08d754e9f232"
        And the response status should be 200
