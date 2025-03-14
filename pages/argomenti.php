<?php
ob_start();

// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/argomento.php';
include_once '../models/esame.php';
include_once '../models/piano_di_studio.php';

//
// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='message error'>Problema di connessione al database.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Inizializza modelli
$argomento = new Argomento($db);
$esame = new Esame($db);
$piano = new PianoDiStudio($db);

// Parametri GET
$esame_id = isset($_GET['esame_id']) ? $_GET['esame_id'] : null;
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;
// Inizializza esame_info
$esame_info = null;

// Carica le informazioni sull'esame se è specificato un ID
if ($esame_id) {
    $esame->id = $esame_id;
    $esame_info = $esame->readOne();
    
    if (empty($esame_info)) {
        echo "<div class='message error'>Esame non trovato.</div>";
        include_once '../ui/includes/footer.php';
        exit;
    }
}
// Includi handler per le operazioni CRUD
include_once 'handlers/argomento_handler.php';

// Includi breadcrumb
include_once 'components/shared/breadcrumb.php';

// Mostra messaggio
if (!empty($message)) {
    echo "<div class='message {$message_class}'>{$message}</div>";
}

// Intestazione pagina
echo "<div class='header-with-button'>";
echo "<h2>Argomenti" . ($esame_id ? " dell'Esame: " . htmlspecialchars($esame_info['nome']) : "") . "</h2>";

// Pulsante di aggiunta condizionale
if ($esame_id && verificaPermessiPiano($db, $esame_id)) {
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Argomento</button>";
}
echo "</div>";

// Recupera argomenti
$stmt = $esame_id ? $argomento->readByEsame($esame_id) : $argomento->readAll();
$num = $stmt->rowCount();

// Visualizzazione argomenti
if ($num > 0) {
    echo "<ul class='item-list'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        echo "<li class='importance-{$livello_importanza}'>";
        echo "<div class='item-title'>" . htmlspecialchars($titolo) . "</div>";
        echo "<div class='item-meta'>Importanza: {$livello_importanza}</div>";
        echo "<div class='item-description'>" . htmlspecialchars($descrizione) . "</div>";
        
       echo "<div class='item-actions'>";
echo "<a href='view_pages/view_argomento.php?id={$id}'>Visualizza</a> | "; // Aggiungi questo link
echo "<a href='sottoargomenti.php?argomento_id={$id}'>Sottoargomenti</a>";

        // Azioni di modifica/eliminazione condizionali
        if ($esame_id && verificaPermessiPiano($db, $esame_id)) {
            echo " | <a href='?edit={$id}&esame_id={$esame_id}'>Modifica</a>";
            echo " | <a href='?delete={$id}&esame_id={$esame_id}' onclick='return confirm(\"Sei sicuro di voler eliminare questo argomento?\");'>Elimina</a>";
        }
        echo "</div></li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nessun argomento trovato.</p>";
}

// Includi i form
if ($edit_id) {
    include_once 'components/forms/edit_argomento.php';
} else {
    include_once 'components/forms/create_argomento.php';
}
/*  //Gestione dei commenti
if ($esame_id && isset($argomento->id)) {
    $risultato_commenti = gestioneCommentiArgomenti($db, $esame_id, $argomento->id);
    
    // Se c'è un risultato con redirect, esegui il redirect
    if ($risultato_commenti && isset($risultato_commenti['redirect'])) {
        header("Location: " . $risultato_commenti['redirect']);
        exit;
    }
    
    // Mostra eventuali messaggi
    if ($risultato_commenti && !empty($risultato_commenti['message'])) {
        echo "<div class='message {$risultato_commenti['message_class']}'>{$risultato_commenti['message']}</div>";
    }
    
    // Rendering dei commenti
    renderCommentiArgomenti($db, $esame_id, $argomento->id);
} */
ob_end_flush();

include_once '../ui/includes/footer.php';
?>