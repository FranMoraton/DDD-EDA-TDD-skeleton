<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Adapter\RestApi;

use App\Users\Application\Command\Users\Create\CreateUserCommand;
use App\Users\Application\Command\Users\Remove\RemoveUserCommand;
use App\Users\Application\Command\Users\Update\UpdateUserCommand;
use App\Users\Application\Query\Users\ById\GetByIdQuery;
use App\System\Infrastructure\Adapter\RestApi\BusController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UsersController extends BusController
{
    public function create(Request $request): JsonResponse
    {
        $requestContent = $this->getRequestBody($request);

        $this->publishSyncCommand(
            CreateUserCommand::create(
                $requestContent->get('id'),
                $requestContent->get('email'),
                $requestContent->get('role'),
                $requestContent->get('password'),
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
            UpdateUserCommand::create(
                $request->attributes->get('id'),
                $requestContent->get('email'),
                $requestContent->get('role'),
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

        $result = $this->publishQuery(GetByIdQuery::create($id));

        return new JsonResponse(
            $result,
            Response::HTTP_OK
        );
    }

    public function remove(Request $request): JsonResponse
    {
        $id = $request->attributes->get('id');

        $this->publishSyncCommand(RemoveUserCommand::create($id));

        return new JsonResponse(
            null,
            Response::HTTP_OK
        );
    }
}
