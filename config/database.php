<?php
class Database {
    private $host = "sql308.infinityfree.com";
    private $db_name = "if0_40310665_library_database";
    private $username = "if0_40310665";
    private $password = "Stinbora26."; // Replace with your InfinityFree account password
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>