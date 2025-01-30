<?php

namespace App\Controller;

use App\DTO\CreateDTO;
use App\Repository\CatalogRepository;
use Slim\Psr7\Request;
use Slim\Psr7\Response;


class CatalogController
{
    public function __construct(
        private CatalogRepository $catalogRepository,
    ) {}


    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $currentPage = (int)($params['page'] ?? 1);  // Поточна сторінка
        $code =  $params['code'] ?? null;  // Пошук по коду
        $address = $params['address'] ?? null;  // Пошук адреси

        $paginationResult = $this->catalogRepository->paginate($currentPage, $code, $address);

        $response->getBody()->write(json_encode($paginationResult));

        return $response->withHeader('Content-Type', 'application/json');
    }


    public function create(Request $request, Response $response, $args): Response
    {
        $dto = CreateDTO::fromRequest($request);

        if (!$dto->isValid()) {
            return $response->withStatus(422);
        }

        $this->catalogRepository->create($dto);

        return $response->withStatus(201);
    }


    public function delete(Request $request, Response $response, $args): Response
    {
        $code = $args['code'];
        $this->catalogRepository->delete($code);

        return $response;
    }
}