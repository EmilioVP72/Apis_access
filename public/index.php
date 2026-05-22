<?php
use Slim\Factory\AppFactory;
use App\Controllers\UserController;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath("");
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$app->get('/api/v1/users/legacy', [UserController::class, 'getLegacyUsers']);
$app->get('/api/v1/users/{id}', [UserController::class, 'getUserById']);
$app->put('/api/v1/users/{id}', [UserController::class, 'updateUser']);
$app->delete('/api/v1/users/legacy/{id}', [UserController::class, 'deleteLegacyUser']);
$app->delete('/api/v1/users/bulk', [UserController::class, 'deleteBulkUsers']);


$app->run();