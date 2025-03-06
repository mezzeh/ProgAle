<?php
include_once 'config/database.php';
include_once 'models/piano_di_studio.php';

// Test di connessione
$database = new Database();
$db = $database->getConnection();

if($db) {
    echo "<p>Connessione al database riuscita!</p>";
    
    // Test di creazione piano di studio
    $piano = new PianoDiStudio($db);
    $piano->nome = "Piano di Test";
    $piano->descrizione = "Piano creato per testare il sistema";
    
    if($piano->create()) {
        echo "<p>Piano di studio creato con successo!</p>";
        
        // Leggi tutti i piani
        $stmt = $piano->readAll();
        echo "<h2>Piani di Studio</h2>";
        echo "<ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            echo "<li>$nome - $descrizione</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Impossibile creare il piano di studio.</p>";
    }
} else {
    echo "<p>Problema di connessione al database.</p>";
}
?>