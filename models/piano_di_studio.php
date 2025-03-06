<?php
class PianoDiStudio {
    private $conn;
    private $table_name = "piani_di_studio";
    
    public $id;
    public $nome;
    public $descrizione;
    public $data_creazione;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Crea nuovo piano di studio
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nome=:nome, descrizione=:descrizione";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizzazione
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descrizione = htmlspecialchars(strip_tags($this->descrizione));
        
        // Binding
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descrizione", $this->descrizione);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Leggi tutti i piani
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY data_creazione DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Leggi singolo piano
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nome = $row['nome'];
            $this->descrizione = $row['descrizione'];
            $this->data_creazione = $row['data_creazione'];
            return true;
        }
        
        return false;
    }
    
    // Aggiorna piano
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nome = :nome, descrizione = :descrizione
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizzazione
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descrizione = htmlspecialchars(strip_tags($this->descrizione));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Binding
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descrizione', $this->descrizione);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Elimina piano
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>