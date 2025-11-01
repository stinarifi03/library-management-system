<?php
require_once __DIR__ . '/../config/database.php';

class LibrarySettings {
    private $conn;
    private $table_name = "library_settings";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get a specific setting
    public function getSetting($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : null;
    }

    // Update a setting
    public function updateSetting($key, $value) {
        $query = "UPDATE " . $this->table_name . " SET setting_value = ? WHERE setting_key = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $value);
        $stmt->bindParam(2, $key);
        
        return $stmt->execute();
    }

    // Get all settings
    public function getAllSettings() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row;
        }
        return $settings;
    }

    // Get fine per day amount
    public function getFinePerDay() {
        $fine = $this->getSetting('fine_per_day');
        return $fine ? floatval($fine) : 1.00;
    }

    // Get max borrow days
    public function getMaxBorrowDays() {
        $days = $this->getSetting('max_borrow_days');
        return $days ? intval($days) : 14;
    }
}
?>