<?php
namespace App\Models;

use PDO;

class Userinfo {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getLegacyUsers() {
        $query = "
            SELECT
                userid as id, 
                name, 
                lastname, 
                identitycard as noCtrl,
                Card as card, 
                card_number_type, 
                Gender,  
                create_time 
            FROM userinfo 
            WHERE create_time <= DATE_SUB(CURDATE(), INTERVAL 6 YEAR)
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
    public function deleteLegacyUser($id) {
        $query = "
            DELETE FROM userinfo 
            WHERE (Card = :id OR userid = :id) 
              AND create_time <= DATE_SUB(CURDATE(), INTERVAL 6 YEAR)
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }
    public function getUserById($id) {
        $query = "
            SELECT
                userid as id, 
                name, 
                lastname, 
                identitycard as noCtrl,
                Card as card, 
                card_number_type, 
                Gender,  
                create_time 
            FROM userinfo 
            WHERE Card = :id OR userid = :id
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteUsersBulk(array $identifiers) {
        if (empty($identifiers)) {
            return 0;
        }

        $inQuery = implode(',', array_fill(0, count($identifiers), '?'));

        $query = "
            DELETE FROM userinfo 
            WHERE Card IN ($inQuery) 
               OR identitycard IN ($inQuery)
               OR userid IN ($inQuery)
        ";

        $stmt = $this->conn->prepare($query);
        
        $params = array_merge($identifiers, $identifiers, $identifiers);
        $stmt->execute($params);

        return $stmt->rowCount();
    }
}

