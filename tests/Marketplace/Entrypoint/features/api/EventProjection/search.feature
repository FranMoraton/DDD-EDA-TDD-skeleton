Feature: search events

    Scenario: search events
        Given the environment is clean
        And user is authenticated
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
            },
            {
                "id": "eb0c5dc8-1c41-4f15-96d6-3fc5da053863",
                "title": "Los morancos",
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
        When I send a "GET" request to "/search?starts_at=2021-06-30T21:00:00Z&ends_at=2021-06-30T22:00:00Z"
        Then the response status should be 200
        And the JSON response should be:
        """
        {
            "data": {
                "events": [
                    {
                        "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                        "title": "Camela en concierto",
                        "start_date": "2021-06-30",
                        "start_time": "21:00:00",
                        "end_date": "2021-06-30",
                        "end_time": "22:00:00",
                        "min_price": 0,
                        "max_price": 30
                    },
                    {
                        "id": "eb0c5dc8-1c41-4f15-96d6-3fc5da053863",
                        "title": "Los morancos",
                        "start_date": "2021-06-30",
                        "start_time": "21:00:00",
                        "end_date": "2021-06-30",
                        "end_time": "22:00:00",
                        "min_price": 0,
                        "max_price": 30
                    }
                ]
            },
            "error": null
        }
        """

    Scenario: search events with 1 items per page
        Given the environment is clean
        And user is authenticated
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
            },
            {
                "id": "eb0c5dc8-1c41-4f15-96d6-3fc5da053863",
                "title": "Los morancos",
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
        When I send a "GET" request to "/search?starts_at=2021-06-30T21:00:00Z&ends_at=2021-06-30T22:00:00Z&items_per_page=1"
        Then the response status should be 200
        And the JSON response should be:
        """
        {
            "data": {
                "events": [
                    {
                        "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                        "title": "Camela en concierto",
                        "start_date": "2021-06-30",
                        "start_time": "21:00:00",
                        "end_date": "2021-06-30",
                        "end_time": "22:00:00",
                        "min_price": 0,
                        "max_price": 30
                    }
                ]
            },
            "error": null
        }
        """

    Scenario: search events from page 1 without limits
        Given the environment is clean
        And user is authenticated
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
            },
            {
                "id": "eb0c5dc8-1c41-4f15-96d6-3fc5da053863",
                "title": "Los morancos",
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
        When I send a "GET" request to "/search?starts_at=2021-06-30T21:00:00Z&ends_at=2021-06-30T22:00:00Z&page=1"
        Then the response status should be 200
        And the JSON response should be:
        """
        {
            "data": {
                "events": [
                    {
                        "id": "e56bf1de-2838-4755-8dd1-08d754e9f232",
                        "title": "Camela en concierto",
                        "start_date": "2021-06-30",
                        "start_time": "21:00:00",
                        "end_date": "2021-06-30",
                        "end_time": "22:00:00",
                        "min_price": 0,
                        "max_price": 30
                    },
                    {
                        "id": "eb0c5dc8-1c41-4f15-96d6-3fc5da053863",
                        "title": "Los morancos",
                        "start_date": "2021-06-30",
                        "start_time": "21:00:00",
                        "end_date": "2021-06-30",
                        "end_time": "22:00:00",
                        "min_price": 0,
                        "max_price": 30
                    }
                ]
            },
            "error": null
        }
        """

    Scenario: search events with page 0 and items_per_page 0
        Given the environment is clean
        And user is authenticated
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
            },
            {
                "id": "eb0c5dc8-1c41-4f15-96d6-3fc5da053863",
                "title": "Los morancos",
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
        When I send a "GET" request to "/search?starts_at=2021-06-30T21:00:00Z&ends_at=2021-06-30T22:00:00Z&page=0&items_per_page=0"
        Then the response status should be 400
        And the JSON response should be:
        """
        {
            "error": {
                "code": 400,
                "message": "InvalidArgumentException"
            },
            "data": null
        }
        """