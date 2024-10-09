<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Adapter\RestApi;

use App\Lotr\Application\Command\Factions\Create\CreateFactionCommand;
use App\Lotr\Application\Command\Factions\Remove\RemoveFactionCommand;
use App\Lotr\Application\Query\Factions\ById\GetByIdQuery;
use App\System\Infrastructure\Adapter\RestApi\BusController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FactionsController extends BusController
{
    public function create(Request $request): JsonResponse
    {
        $requestContent = $this->getRequestBody($request);

        $this->publishSyncCommand(
            new CreateFactionCommand(
                $requestContent->get('id'),
                $requestContent->get('name'),
                $requestContent->get('description'),
            ),
        );

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }

    public function byId(Request $request): JsonResponse
    {
        $id = $request->attributes->get('id');

        $result = $this->publishQuery(new GetByIdQuery($id));

        return new JsonResponse(
            $result,
            Response::HTTP_OK
        );
    }

    public function remove(Request $request): JsonResponse
    {
        $id = $request->attributes->get('id');

        $this->publishSyncCommand(new RemoveFactionCommand($id));

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }
}
