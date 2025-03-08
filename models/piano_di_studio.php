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
    
    // In piano_di_studio.php aggiungi:
public $user_id;
public $visibility;

// Modificare il metodo create:
public function create() {
    $query = "INSERT INTO " . $this->table_name . " 
              SET nome=:nome, descrizione=:descrizione, 
              user_id=:user_id, visibility=:visibility";
    // ...binding aggiuntivi...
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->bindParam(":visibility", $this->visibility);
    // ...
}

// Aggiungere un metodo per ottenere piani pubblici:
public function readPublic() {
    $query = "SELECT * FROM " . $this->table_name . " 
              WHERE visibility = 'public'
              ORDER BY data_creazione DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt;
}

// Aggiungere un metodo per ottenere i piani dell'utente:
public function readByUser($user_id) {
    $query = "SELECT * FROM " . $this->table_name . " 
              WHERE user_id = :user_id
              ORDER BY data_creazione DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    return $stmt;
}
    
    // READ ALL
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY data_creazione DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readAllPaginated($from_record_num, $records_per_page) {
        $query = "SELECT * FROM " . $this->table_name . "
                  ORDER BY data_creazione DESC
                  LIMIT :from_record_num, :records_per_page";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":from_record_num", $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // READ ONE
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->nome = $row['nome'];
            $this->descrizione = $row['descrizione'];
            $this->data_creazione = $row['data_creazione'];
            return $row;
        }
        return [];
    }
    
    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nome = :nome, descrizione = :descrizione
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descrizione = htmlspecialchars(strip_tags($this->descrizione));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':descrizione', $this->descrizione);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    
    // DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

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

  public function search($keyword) {
    // Query SQL con ricerca LIKE
    $query = "SELECT id, nome, descrizione FROM " . $this->table_name . 
             " WHERE nome LIKE ? OR descrizione LIKE ? ORDER BY nome";
    
    // Preparazione della query
    $stmt = $this->conn->prepare($query);
    
    // Formattazione parola chiave
    $keyword = "%{$keyword}%";
    
    // Binding dei parametri
    $stmt->bindParam(1, $keyword);
    $stmt->bindParam(2, $keyword);
    
    // Esecuzione della query
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
    
}
