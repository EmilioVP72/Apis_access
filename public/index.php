<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath("");
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$app->get('/test-db', function (Request $request, Response $response) {
    $host = getenv('DB_HOST');
    $db   = getenv('DB_NAME');
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", getenv('DB_USER'), getenv('DB_PASS'));
        $response->getBody()->write(json_encode(["status" => "Conectado a MySQL"]));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
    }
    
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();