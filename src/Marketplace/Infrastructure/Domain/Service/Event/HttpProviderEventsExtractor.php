<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Domain\Service\Event;

use App\Marketplace\Domain\Service\Event\ProviderEventsExtractor\ProviderEventsExtractor;
use App\System\Infrastructure\Service\JsonSerializer;
use GuzzleHttp\Client;

final readonly class HttpProviderEventsExtractor implements ProviderEventsExtractor
{
    public function __construct(private Client $providerClient)
    {
    }

    public function execute(string $providerId): array
    {
        $xmlResponse = $this->providerClient->request(
            'GET',
            "api/events",
            [
                'headers' => [
                    'Accept' => 'application/xml',
                ],
            ]
        );
        $xmlContent = $xmlResponse->getBody()->getContents();

        $xmlObject = \simplexml_load_string($xmlContent);

        $json = JsonSerializer::encode($xmlObject);
        $dataArray = JsonSerializer::decodeArray($json);

        $dataArray = $this->cleanXmlAttributes($dataArray);

        return $dataArray;
    }

    private function cleanXmlAttributes(array $data): array
    {
        $baseEvents = $data['output']['base_event'];

        if (!is_array(reset($baseEvents))) {
            $baseEvents = [$baseEvents];
        }

        $result = [];

        foreach ($baseEvents as $baseEvent) {
            $baseEvent = $this->processAttributes($baseEvent);

            $event = $this->processAttributes($baseEvent['event']);

            $zones = $event['zone'];
            if (isset($zones['@attributes'])) {
                $zones = [$zones];
            }

            foreach ($zones as &$zone) {
                $zone = $this->processAttributes($zone);
            }
            $event['zones'] = $zones;
            unset($event['zone']);

            $combinedEvent = array_merge($baseEvent, $event);

            unset($combinedEvent['event']);

            $result[] = $combinedEvent;
        }

        return $result;
    }

    private function processAttributes(array &$item): array
    {
        $attributes = isset($item['@attributes']) ? $item['@attributes'] : [];
        unset($item['@attributes']);
        return array_merge($attributes, $item);
    }
}
