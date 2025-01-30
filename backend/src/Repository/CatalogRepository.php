<?php

namespace App\Repository;

use App\DTO\CreateDTO;
use App\Helpers\PaginatorHelper;
use PDO;

class CatalogRepository
{
    private int $limit = 48;

    public function __construct(private PDO $pdo) {}

    public function getItems(int $offset, ?string $code = null, ?string $office_name = null): array
    {
        $queryBuilder = new QueryBuilder($this->pdo);

        return $queryBuilder
            ->addWhere('code', $code)
            ->addWhere('office_name', $office_name, true)
            ->addLimitAndOffset($this->limit, $offset)
            ->getItems();
    }

    public function setPageLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getItemsCount(?string $code = null, ?string $office_name = null): int
    {
        $queryBuilder = new QueryBuilder($this->pdo);

        return $queryBuilder
            ->addWhere('code', $code)
            ->addWhere('office_name', $office_name, true)
            ->getItemsCount();
    }

    public function paginate(int $currentPage, ?string $code = null, ?string $office_name = null): array
    {
        $totalItems = $this->getItemsCount($code, $office_name);
        $offset = PaginatorHelper::getOffset($this->limit, $currentPage);

        $items = $this->getItems($offset, $code, $office_name);

        $pagination = PaginatorHelper::getPagination($this->limit, $currentPage, $totalItems);

        return [
            'items' => $items,
            'pagination' => $pagination
        ];
    }

    public function create(CreateDTO $dto): bool
    {
        $sql = "INSERT INTO postal_codes (code, region, district, district_old, city, region_slug, district_slug, city_slug, office_name, office_slug, office_code) 
                VALUES (:code, :region, :district, :district_old, :city, :region_slug, :district_slug, :city_slug, :office_name, :office_slug, :office_code)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $dto->code);
        $stmt->bindParam(':region', $dto->region);
        $stmt->bindParam(':district', $dto->district);
        $stmt->bindParam(':district_old', $dto->district_old);
        $stmt->bindParam(':city', $dto->city);
        $stmt->bindParam(':region_slug', $dto->region_slug);
        $stmt->bindParam(':district_slug', $dto->district_slug);
        $stmt->bindParam(':city_slug', $dto->city_slug);
        $stmt->bindParam(':office_name', $dto->office_name);
        $stmt->bindParam(':office_slug', $dto->office_slug);
        $stmt->bindParam(':office_code', $dto->office_code);

        return $stmt->execute();
    }

    public function delete(string $code): bool
    {
        $sql = "DELETE FROM postal_codes WHERE code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $code);

        return $stmt->execute();
    }
}