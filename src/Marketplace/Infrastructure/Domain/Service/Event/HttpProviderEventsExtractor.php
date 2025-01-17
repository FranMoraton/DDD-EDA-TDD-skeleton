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
        // Procesar base_event manualmente
        if (isset($data['output']['base_event'])) {
            $baseEvents = $data['output']['base_event'];
            if (!is_array(reset($baseEvents))) {
                // Si no es un array de objetos, lo envolvemos en uno
                $baseEvents = [$baseEvents];
            }

            foreach ($baseEvents as &$baseEvent) {
                $baseEvent = $this->processAttributes($baseEvent);

                // Procesar evento dentro de base_event
                if (isset($baseEvent['event'])) {
                    $baseEvent['event'] = $this->processAttributes($baseEvent['event']);

                    // Procesar zonas dentro de evento
                    if (isset($baseEvent['event']['zone'])) {
                        $zones = $baseEvent['event']['zone'];
                        if (isset($zones['@attributes'])) {
                            // Si es un único objeto de zona, lo envolvemos en un array
                            $zones = [$zones];
                        }

                        foreach ($zones as &$zone) {
                            $zone = $this->processAttributes($zone);
                        }
                        $baseEvent['event']['zone'] = $zones;
                    }
                }
            }

            $data['output']['base_event'] = $baseEvents;
        }

        return $data['output']['base_event'];
    }

    private function processAttributes(array &$item): array
    {
        $attributes = isset($item['@attributes']) ? $item['@attributes'] : [];
        unset($item['@attributes']);
        return array_merge($attributes, $item);
    }
}
