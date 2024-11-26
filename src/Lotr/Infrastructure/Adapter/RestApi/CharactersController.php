<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Adapter\RestApi;

use App\Lotr\Application\Command\Characters\Create\CreateCharacterCommand;
use App\Lotr\Application\Command\Characters\Remove\RemoveCharacterCommand;
use App\Lotr\Application\Command\Characters\Update\UpdateCharacterCommand;
use App\Lotr\Application\Query\Characters\ById\GetByIdQuery;
use App\System\Infrastructure\Adapter\RestApi\BusController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CharactersController extends BusController
{
    public function create(Request $request): JsonResponse
    {
        $requestContent = $this->getRequestBody($request);

        $this->publishSyncCommand(
            new CreateCharacterCommand(
                $requestContent->get('id'),
                $requestContent->get('name'),
                $requestContent->get('birth_date'),
                $requestContent->get('kingdom'),
                $requestContent->get('equipment_id'),
                $requestContent->get('faction_id'),
            ),
        );

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }

    public function update(Request $request): JsonResponse
    {
        $requestContent = $this->getRequestBody($request);

        $this->publishSyncCommand(
            new UpdateCharacterCommand(
                $request->attributes->get('id'),
                $requestContent->get('name'),
                $requestContent->get('birth_date'),
                $requestContent->get('kingdom'),
                $requestContent->get('equipment_id'),
                $requestContent->get('faction_id'),
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

        $this->publishSyncCommand(new RemoveCharacterCommand($id));

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }
}
