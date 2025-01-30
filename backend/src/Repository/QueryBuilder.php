<?php

namespace App\Repository;

use PDO;

class QueryBuilder
{
    private array $params = [];
    private int $limit = 48;  // Дефолтне значення для ліміту
    private int $offset = 0;  // Дефолтне значення для офсету

    // Sql шаблони
    private string $sqlSelectTpl = "SELECT * FROM postal_codes {where} ORDER BY code ASC {limit} {offset}";
    private string $sqlCountTpl = "SELECT COUNT(*) FROM postal_codes {where}";

    public function __construct(private PDO $pdo) {}

    /**
     * Додаємо умови до WHERE
     */
    public function addWhere(string $param, ?string $value, bool $like = false): self
    {
        if ($value) {
            $this->params[] = [
                'param' => $param,
                'value' => $like ? "%$value%" : $value,
                'like'  => $like
            ];
        }

        return $this;
    }

    /**
     * Додаємо ліміт і офсет
     */
    public function addLimitAndOffset(int $limit, int $offset): self
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * Формуємо WHERE частину
     */
    private function buildWhere(): string
    {
        $whereClauses = array_map(function ($param) {
            $operator = $param['like'] ? "LIKE" : "=";
            return "{$param['param']} $operator :{$param['param']}";
        }, $this->params);

        return $whereClauses ? "WHERE " . implode(' AND ', $whereClauses) : '';
    }

    /**
     * Підготовка SQL-запиту для отримання елементів
     */
    private function prepareSelectStatement(): \PDOStatement
    {
        $where = $this->buildWhere();
        $sql = str_replace(['{where}', '{limit}', '{offset}'], [$where, "LIMIT :limit", "OFFSET :offset"], $this->sqlSelectTpl);

        $stmt = $this->pdo->prepare($sql);

        foreach ($this->params as $param) {
            $stmt->bindValue(':' . $param['param'], $param['value'], is_int($param['value']) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $this->offset, PDO::PARAM_INT);

        return $stmt;
    }

    /**
     * Підготовка SQL-запиту для підрахунку кількості елементів
     */
    private function prepareCountStatement(): \PDOStatement
    {
        $where = $this->buildWhere();
        $sql = str_replace('{where}', $where, $this->sqlCountTpl);

        $stmt = $this->pdo->prepare($sql);

        foreach ($this->params as $param) {
            $stmt->bindValue(':' . $param['param'], $param['value'], is_int($param['value']) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $stmt;
    }

    /**
     * Отримати елементи з бази даних
     */
    public function getItems(): array
    {
        $stmt = $this->prepareSelectStatement();
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Отримати кількість елементів в базі даних
     */
    public function getItemsCount(): int
    {
        $stmt = $this->prepareCountStatement();
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}