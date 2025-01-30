<?php

namespace Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API поштових індексів",
 *     version="1.0.0",
 *     description="API для роботи з поштовими індексами, створенням та видаленням записів"
 * )
 */

/**
 * @OA\PathItem(
 *     path="/"
 * )
 */

/**
 * @OA\Get(
 *     path="/",
 *     summary="Отримання поштових індексів",
 *     description="Отримання списку поштових індексів з можливістю фільтрації за параметрами",
 *     tags={"Поштові індекси"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Номер сторінки для пагінації",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="code",
 *         in="query",
 *         description="Пошук за поштовим індексом",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="address",
 *         in="query",
 *         description="Пошук за адресою відділення (пошук за допомогою LIKE)",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Список поштових індексів",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="code", type="string"),
 *                     @OA\Property(property="region", type="string"),
 *                     @OA\Property(property="district", type="string"),
 *                     @OA\Property(property="district_old", type="string", nullable=true),
 *                     @OA\Property(property="city", type="string"),
 *                     @OA\Property(property="region_slug", type="string"),
 *                     @OA\Property(property="district_slug", type="string"),
 *                     @OA\Property(property="city_slug", type="string"),
 *                     @OA\Property(property="office_name", type="string"),
 *                     @OA\Property(property="office_slug", type="string"),
 *                     @OA\Property(property="office_code", type="string")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="currentPage", type="integer"),
 *                 @OA\Property(property="totalPages", type="integer"),
 *                 @OA\Property(property="totalItems", type="integer"),
 *                 @OA\Property(property="limit", type="integer")
 *             )
 *         )
 *     )
 * )
 */

class catalog {}

/**
 * @OA\Post(
 *     path="/",
 *     summary="Створення поштового індексу",
 *     description="Створення нового поштового індексу з детальною інформацією",
 *     tags={"Поштові індекси"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"code", "region", "district", "city", "region_slug", "district_slug", "city_slug", "office_name", "office_slug", "office_code"},
 *             @OA\Property(property="code", type="string", maxLength=6),
 *             @OA\Property(property="region", type="string", maxLength=30),
 *             @OA\Property(property="district", type="string", maxLength=30),
 *             @OA\Property(property="city", type="string", maxLength=30),
 *             @OA\Property(property="region_slug", type="string", maxLength=30),
 *             @OA\Property(property="district_slug", type="string", maxLength=30),
 *             @OA\Property(property="city_slug", type="string", maxLength=30),
 *             @OA\Property(property="office_name", type="string", maxLength=100),
 *             @OA\Property(property="office_slug", type="string", maxLength=100),
 *             @OA\Property(property="office_code", type="string", maxLength=6)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Поштовий індекс успішно створений",
 *         @OA\JsonContent(type="object")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Помилка валідації",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Некоректні дані")
 *         )
 *     )
 * )
 */

class catalogCreate {}

/**
 * @OA\Delete(
 *     path="/{code}",
 *     summary="Видалення поштового індексу",
 *     description="Видалення поштового індексу за кодом",
 *     tags={"Поштові індекси"},
 *     @OA\Parameter(
 *         name="code",
 *         in="path",
 *         description="Код поштового індексу, який потрібно видалити",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Поштовий індекс успішно видалено",
 *         @OA\JsonContent(type="object")
 *     )
 * )
 */

class catalogDelete {}

