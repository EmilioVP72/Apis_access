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

        $userId = trim($args['id'] ?? '');
        if (empty($userId)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "El ID proporcionado no es válido."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

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

        $userId = trim($args['id'] ?? '');
        if (empty($userId)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "El ID proporcionado no es válido."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

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

        // Limpiar el arreglo de valores nulos/vacíos y evitar inyección de excesivos parámetros
        $identifiers = array_filter(array_map('trim', $identifiers));

        if (empty($identifiers)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "El arreglo de identificadores no contiene valores válidos."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (count($identifiers) > 100) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Límite excedido. Puedes eliminar un máximo de 100 usuarios por petición."
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

    public function updateUser(Request $request, Response $response, array $args) {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Database connection failed."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $userId = trim($args['id'] ?? '');
        if (empty($userId)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "El ID proporcionado no es válido."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $data = $request->getParsedBody() ?? [];

        if (empty($data)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "No se enviaron datos para actualizar."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $userinfo = new Userinfo($db);
        
        try {
            $db->beginTransaction();
            $affectedRows = $userinfo->updateUser($userId, $data);
            $db->commit();

            if ($affectedRows > 0) {
                $response->getBody()->write(json_encode([
                    "status" => "success",
                    "message" => "Usuario actualizado correctamente."
                ]));
            } else {
                // PDO rowCount es 0 si los datos eran los mismos o el usuario no existe.
                $response->getBody()->write(json_encode([
                    "status" => "success",
                    "message" => "No se realizaron cambios o el usuario no existe."
                ]));
            }
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $db->rollBack();
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "Error al actualizar el usuario: " . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
