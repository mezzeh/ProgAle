<?php
class Requisito {
    private $conn;
    private $table_name = "requisiti";
    
    public $id;
    public $esercizio_id;
    public $descrizione;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET esercizio_id=:esercizio_id, descrizione=:descrizione";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizzazione
        $this->esercizio_id = htmlspecialchars(strip_tags($this->esercizio_id));
        $this->descrizione = htmlspecialchars(strip_tags($this->descrizione));
        
        // Binding
        $stmt->bindParam(":esercizio_id", $this->esercizio_id);
        $stmt->bindParam(":descrizione", $this->descrizione);
        
        return $stmt->execute();
    }
    
    // READ ALL
    public function readAll() {
        $query = "SELECT r.*, e.titolo as esercizio_titolo 
                  FROM " . $this->table_name . " r
                  LEFT JOIN esercizi e ON r.esercizio_id = e.id
                  ORDER BY e.titolo ASC, r.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // READ BY ESERCIZIO
    public function readByEsercizio($esercizio_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE esercizio_id = :esercizio_id
                  ORDER BY id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":esercizio_id", $esercizio_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    // READ ONE
    public function readOne() {
        $query = "SELECT r.*, e.titolo as esercizio_titolo 
                  FROM " . $this->table_name . " r
                  LEFT JOIN esercizi e ON r.esercizio_id = e.id
                  WHERE r.id = :id 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->esercizio_id = $row['esercizio_id'];
            $this->descrizione = $row['descrizione'];
            return true;
        }
        
        return false;
    }
    
    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET esercizio_id = :esercizio_id, descrizione = :descrizione
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizzazione
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->esercizio_id = htmlspecialchars(strip_tags($this->esercizio_id));
        $this->descrizione = htmlspecialchars(strip_tags($this->descrizione));
        
        // Binding
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":esercizio_id", $this->esercizio_id);
        $stmt->bindParam(":descrizione", $this->descrizione);
        
        return $stmt->execute();
    }
    
    // DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // DELETE MULTIPLE
    public function deleteMultiple($ids) {
        if (empty($ids)) return false;
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $query = "DELETE FROM " . $this->table_name . " WHERE id IN ($inQuery)";
        $stmt = $this->conn->prepare($query);
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }
    
    // SEARCH
    public function search($keywords) {
        $query = "SELECT r.*, e.titolo as esercizio_titolo
                  FROM " . $this->table_name . " r
                  LEFT JOIN esercizi e ON r.esercizio_id = e.id
                  WHERE r.descrizione LIKE :keywords
                  ORDER BY e.titolo ASC, r.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(":keywords", $keywords);
        $stmt->execute();
        
        return $stmt;
    }
    
    // COUNT
    public function count() {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }
    
    // COUNT BY ESERCIZIO
    public function countByEsercizio($esercizio_id) {
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . " WHERE esercizio_id = :esercizio_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":esercizio_id", $esercizio_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }
}
?>