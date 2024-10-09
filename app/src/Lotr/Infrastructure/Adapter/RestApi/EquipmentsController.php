<?php

declare(strict_types=1);

namespace App\Lotr\Infrastructure\Adapter\RestApi;

use App\Lotr\Application\Command\Equipments\Create\CreateEquipmentCommand;
use App\Lotr\Application\Command\Equipments\Remove\RemoveEquipmentCommand;
use App\Lotr\Application\Command\Equipments\Update\UpdateEquipmentCommand;
use App\Lotr\Application\Query\Equipments\ById\GetByIdQuery;
use App\System\Infrastructure\Adapter\RestApi\BusController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EquipmentsController extends BusController
{
    public function create(Request $request): JsonResponse
    {
        $requestContent = $this->getRequestBody($request);

        $this->publishSyncCommand(
            new CreateEquipmentCommand(
                $requestContent->get('id'),
                $requestContent->get('name'),
                $requestContent->get('type'),
                $requestContent->get('made_by'),
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
            new UpdateEquipmentCommand(
                $request->attributes->get('id'),
                $requestContent->get('name'),
                $requestContent->get('type'),
                $requestContent->get('made_by'),
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

        $this->publishSyncCommand(new RemoveEquipmentCommand($id));

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }
}
