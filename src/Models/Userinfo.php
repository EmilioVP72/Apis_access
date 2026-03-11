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
                name, 
                lastname, 
                Card as card, 
                card_number_type, 
                Gender, 
                badgenumber as identifycard, 
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
            WHERE userid = :id 
              AND create_time <= DATE_SUB(CURDATE(), INTERVAL 6 YEAR)
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}

