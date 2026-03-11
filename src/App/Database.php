<?php
namespace App\App;

use PDO;
use PDOException;

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function getConnection() {
        $this->host = getenv('DB_HOST') ?: 'mysql_server';
        $this->db_name = getenv('DB_DATABASE') ?: 'access';
        $this->username = getenv('DB_USER') ?: 'admin';
        $this->password = getenv('DB_PASSWORD') ?: '123';

        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
