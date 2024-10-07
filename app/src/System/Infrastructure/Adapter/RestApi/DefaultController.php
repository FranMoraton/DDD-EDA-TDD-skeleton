<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Adapter\RestApi;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(
            ['alive'],
            Response::HTTP_OK
        );
    }
}
