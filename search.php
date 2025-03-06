<?php
// Includi header
include_once 'ui/includes/header.php';

// Includi file di configurazione e modelli
include_once 'config/database.php';
include_once 'models/piano_di_studio.php';
include_once 'models/esame.php';
include_once 'models/argomento.php';
include_once 'models/sottoargomento.php';
include_once 'models/esercizio.php';
include_once 'models/formula.php';

// Inizializza variabili per messaggi
$message = "";
$message_class = "";

// Verifica se è stata fornita una query di ricerca
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($search_term)) {
    $message = "Inserisci un termine di ricerca.";
    $message_class = "info";
} else {
    // Connessione al database
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        $message = "Problema di connessione al database.";
        $message_class = "error";
    } else {
        // Istanza dei modelli
        $piano = new PianoDiStudio($db);
        $esame = new Esame($db);
        $argomento = new Argomento($db);
        $sottoargomento = new SottoArgomento($db);
        $esercizio = new Esercizio($db);
        $formula = new Formula($db);
        
        // Esegui ricerca in ciascun modello
        $piani_results = $piano->search($search_term);
        $esami_results = $esame->search($search_term);
        $argomenti_results = $argomento->search($search_term);
        $sottoargomenti_results = $sottoargomento->search($search_term);
        $esercizi_results = $esercizio->search($search_term);
        $formule_results = $formula->search($search_term);
        
        // Conta risultati
        $total_results = 
            $piani_results->rowCount() + 
            $esami_results->rowCount() + 
            $argomenti_results->rowCount() + 
            $sottoargomenti_results->rowCount() + 
            $esercizi_results->rowCount() + 
            $formule_results->rowCount();
        
        if ($total_results == 0) {
            $message = "Nessun risultato trovato per: " . htmlspecialchars($search_term);
            $message_class = "info";
        }
    }
}

// Mostra il messaggio se presente
if (!empty($message)) {
    echo "<div class='message $message_class'>$message</div>";
}

// Mostra intestazione di ricerca
echo "<h2>Risultati di ricerca per: " . htmlspecialchars($search_term) . "</h2>";

// Funzione per mostrare i risultati di una tabella
function displayResults($stmt, $title, $link_prefix, $id_field = 'id', $name_field = 'nome', $description_field = 'descrizione') {
    if ($stmt->rowCount() > 0) {
        echo "<h3>$title</h3>";
        echo "<ul class='search-results'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>";
            echo "<div class='item-title'><a href='" . $link_prefix . $row[$id_field] . "'>" . $row[$name_field] . "</a></div>";
            if (isset($row[$description_field]) && !empty($row[$description_field])) {
                echo "<div class='item-description'>" . substr($row[$description_field], 0, 150) . (strlen($row[$description_field]) > 150 ? "..." : "") . "</div>";
            }
            echo "</li>";
        }
        echo "</ul>";
    }
}

// Mostra i risultati se ci sono
if (isset($total_results) && $total_results > 0) {
    // Piani di studio
    displayResults($piani_results, "Piani di Studio", "index.php?edit=");
    
    // Esami
    displayResults($esami_results, "Esami", "esami.php?edit=");
    
    // Argomenti
    displayResults($argomenti_results, "Argomenti", "argomenti.php?edit=", 'id', 'titolo');
    
    // Sottoargomenti
    displayResults($sottoargomenti_results, "Sottoargomenti", "sottoargomenti.php?edit=", 'id', 'titolo');
    
    // Esercizi
    displayResults($esercizi_results, "Esercizi", "esercizi.php?edit=", 'id', 'titolo', 'testo');
    
    // Formule
    displayResults($formule_results, "Formule", "formule.php?edit=");
}

// Includi footer
include_once 'ui/includes/footer.php';
?>