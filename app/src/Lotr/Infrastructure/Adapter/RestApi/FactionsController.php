<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Adapter\RestApi;

use App\Lotr\Application\Command\Factions\Create\CreateFactionCommand;
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

        $this->publishSyncCommand(new CreateFactionCommand());

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }

    public function byId(Request $request): JsonResponse
    {
        $id = $request->attributes->get('id');

        var_dump($id);
        $this->publishQuery(new GetByIdQuery());

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }
}
