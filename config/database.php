<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "sistema_studio";
    private $username = "root";
    private $password = "";
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Errore di connessione: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    // Helper function to get the correct path to a page
function getPagePath($page) {
    if (strpos($_SERVER['PHP_SELF'], 'view_pages') !== false) {
        return "../pages/$page";
    } else {
        return $page;
    }
}
}
?>