<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
include_once 'models/piano_di_studio.php';

// Verifica se è stata fornita una query di ricerca
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($search_term)) {
    echo "<div class='message info'>Inserisci il nome di un piano di studi da cercare.</div>";
} else {
    // Connessione al database
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo "<div class='message error'>Problema di connessione al database.</div>";
    } else {
        // Istanza del modello PianoDiStudio
        $piano = new PianoDiStudio($db);
        
        // Cerca piani di studio che corrispondono al termine di ricerca
        $results = $piano->search($search_term);
        
        if ($results->rowCount() > 0) {
            echo "<h2>Risultati della ricerca per: " . htmlspecialchars($search_term) . "</h2>";
            echo "<p>Seleziona un piano di studi per visualizzare i suoi esami:</p>";
            echo "<ul class='item-list'>";
            
            while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>";
                echo "<div class='item-title'>" . htmlspecialchars($row['nome']) . "</div>";
                echo "<div class='item-description'>" . htmlspecialchars($row['descrizione']) . "</div>";
                echo "<div class='item-actions'>";
                echo "<a href='esami.php?piano_id=" . $row['id'] . "' class='btn-primary'>Visualizza Esami</a>";
                echo "</div>";
                echo "</li>";
            }
            
            echo "</ul>";
        } else {
            echo "<div class='message info'>Nessun piano di studi trovato per: " . htmlspecialchars($search_term) . "</div>";
            echo "<p>Suggerimenti:</p>";
            echo "<ul>";
            echo "<li>Verifica che il nome del piano di studi sia scritto correttamente</li>";
            echo "<li>Prova a usare parole chiave più generiche</li>";
            echo "<li>Puoi <a href='index.php'>visualizzare tutti i piani di studio</a> disponibili</li>";
            echo "</ul>";
        }
    }
}

// Includi footer
include_once 'ui/includes/footer.php';
?>