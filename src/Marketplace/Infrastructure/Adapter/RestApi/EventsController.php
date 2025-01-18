<?php

declare(strict_types=1);

namespace App\Marketplace\Infrastructure\Adapter\RestApi;

use App\Marketplace\Application\Command\Events\BringFromProvider\BringFromProviderCommand;
use App\Marketplace\Application\Query\EventProjections\Search\SearchQuery;
use App\Marketplace\Application\Query\Events\ById\GetByIdQuery;
use App\System\Domain\ValueObject\Uuid;
use App\System\Infrastructure\Adapter\RestApi\BusController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EventsController extends BusController
{
    public function byId(Request $request): JsonResponse
    {
        $id = $request->attributes->get('id');

        $result = $this->publishQuery(GetByIdQuery::create($id));

        return new JsonResponse(
            $result,
            Response::HTTP_OK
        );
    }

    public function bringFromProvider(Request $request): JsonResponse
    {
        $body = $this->getRequestBody($request);

        $this->publishAsync(
            BringFromProviderCommand::create(Uuid::v4()->value()),
        );

        return new JsonResponse(null, Response::HTTP_OK);
    }

    public function search(Request $request): JsonResponse
    {
        $result = $this->publishQuery(
            SearchQuery::create(
                $request->query->get('starts_at'),
                $request->query->get('ends_at'),
                $request->query->get('items_per_page'),
                $request->query->get('page'),
            ),
        );

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
