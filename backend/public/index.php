<?php

use App\Controller\CatalogController;
use DI\Container;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('/config/')->safeLoad();
$dotenv = Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new Container();

// Налаштовуємо контейнер в Slim
AppFactory::setContainer($container);

// Реєструємо зʼеднання бд в контейнері
$container->set(PDO::class, function () {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=utf8",
        $_ENV['MYSQL_HOST'],
        $_ENV['MYSQL_DATABASE']
    );

    $username = $_ENV['MYSQL_USER'];
    $password = $_ENV['MYSQL_PASS'];

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
});

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
 });

$app->get('/', [CatalogController::class, 'index']);
$app->post('/', [CatalogController::class, 'create']);
$app->delete('/{code}', [CatalogController::class, 'delete']);

$app->run();
