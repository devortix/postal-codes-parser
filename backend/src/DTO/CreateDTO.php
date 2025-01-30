<?php

namespace App\DTO;

use Psr\Http\Message\RequestInterface as Request;

class CreateDTO
{
    // Константи для валідації
    public const LENGTH_MAP = [
        'code' => 6,
        'region' => 30,
        'district' => 30,
        'city' => 30,
        'region_slug' => 30,
        'district_slug' => 30,
        'city_slug' => 30,
        'office_name' => 100,
        'office_slug' => 100,
        'office_code' => 6,
    ];

    public const REQUIRED_PARAMS = [
        'code', 'region', 'district', 'city', 'region_slug', 'district_slug', 'city_slug'
    ];

    public function __construct(
        public ?string $code = null,
        public ?string $region = null,
        public ?string $district = null,
        public ?string $district_old = null,
        public ?string $city = null,
        public ?string $region_slug = null,
        public ?string $district_slug = null,
        public ?string $city_slug = null,
        public ?string $office_name = null,
        public ?string $office_slug = null,
        public ?string $office_code = null
    ) {}

    // Створення DTO з запиту
    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getBody()->getContents(), true);

        return new self(
            $data['code'] ?? null,
            $data['region'] ?? null,
            $data['district'] ?? null,
            $data['district_old'] ?? null,
            $data['city'] ?? null,
            $data['region_slug'] ?? null,
            $data['district_slug'] ?? null,
            $data['city_slug'] ?? null,
            $data['office_name'] ?? null,
            $data['office_slug'] ?? null,
            $data['office_code'] ?? null
        );
    }

    // Валідація параметрів
    public function isValid(): bool
    {
        // Перевірка обов'язкових параметрів
        foreach (self::REQUIRED_PARAMS as $param) {
            if (empty($this->$param)) {
                return false;
            }
        }

        // Перевірка на довжину
        foreach (self::LENGTH_MAP as $param => $length) {
            if (!empty($this->$param) && strlen($this->$param) > $length) {
                return false;
            }
        }

        return true;
    }
}