<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\App\Database;
use App\Models\Userinfo;
use PDO;

class UserController {
    
    public function getLegacyUsers(Request $request, Response $response) {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Database connection failed."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $userinfo = new Userinfo($db);
        $stmt = $userinfo->getLegacyUsers();

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }

        $response->getBody()->write(json_encode([
            "status" => "success",
            "count" => count($users),
            "data" => $users
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function deleteLegacyUser(Request $request, Response $response, array $args) {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Database connection failed."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $userId = $args['id'];
        $userinfo = new Userinfo($db);
        
        try {
            $db->beginTransaction();
            $affectedRows = $userinfo->deleteLegacyUser($userId);
            $db->commit();

            if ($affectedRows > 0) {
                $response->getBody()->write(json_encode([
                    "status" => "success",
                    "message" => "Usuario con ID $userId eliminado correctamente."
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode([
                    "status" => "error",
                    "message" => "No se pudo eliminar el usuario. Puede que el ID no exista o el usuario no cumple con el requisito de ser mayor a 6 años."
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } catch (\Exception $e) {
            $db->rollBack();
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Error al procesar la eliminación: " . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function getUserById(Request $request, Response $response, array $args) {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Database connection failed."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $userId = $args['id'];
        $userinfo = new Userinfo($db);
        $user = $userinfo->getUserById($userId);

        if ($user) {
            $response->getBody()->write(json_encode([
                "status" => "success",
                "data" => $user
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Usuario con ID $userId no encontrado."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
    public function deleteBulkUsers(Request $request, Response $response) {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Database connection failed."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $data = $request->getParsedBody();
        $identifiers = $data['identifiers'] ?? [];

        if (!is_array($identifiers) || empty($identifiers)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Please provide an array of identifiers in the 'identifiers' field."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $userinfo = new Userinfo($db);
        
        try {
            $db->beginTransaction();
            $affectedRows = $userinfo->deleteUsersBulk($identifiers);
            $db->commit();

            $response->getBody()->write(json_encode([
                "status" => "success",
                "message" => "$affectedRows usuarios eliminados correctamente."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $db->rollBack();
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Error al procesar la eliminación masiva: " . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
