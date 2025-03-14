<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Includi header
include_once '../ui/includes/header.php';

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/esame.php';
include_once '../models/piano_di_studio.php';
include_once '../models/comments.php';

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<div class='message error'>Problema di connessione al database.</div>";
    include_once '../ui/includes/footer.php';
    exit;
}

// Inizializza modelli
$esame = new Esame($db);
$piano = new PianoDiStudio($db);

// Parametri GET
$piano_id = isset($_GET['piano_id']) ? $_GET['piano_id'] : null;
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;

// Debug output
echo "<!-- Debug Info\n";
echo "Piano ID: " . ($piano_id ? $piano_id : 'Not set') . "\n";
echo "Edit ID: " . ($edit_id ? $edit_id : 'Not set') . "\n";
echo "Session User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "\n";
echo "Is Admin: " . (isset($_SESSION['is_admin']) ? 'Yes' : 'No') . "\n";
echo "-->";

$piano_info = null;
if ($piano_id) {
    $piano->id = $piano_id;
    $piano_info = $piano->readOne();
}

// Includi handler per le operazioni CRUD
include_once 'handlers/esame_handler.php';

// Includi e usa il breadcrumb condiviso
include_once 'components/shared/breadcrumb.php';

if ($piano_id && $piano_info) {
    $breadcrumb_items = [
        ['text' => 'Piani di Studio', 'link' => 'index.php'],
        ['text' => $piano_info['nome']]
    ];
    
    generaBreadcrumb($breadcrumb_items);
}

// Mostra messaggio
if (!empty($message)) {
    echo "<div class='message {$message_class}'>{$message}</div>";
}

// Intestazione pagina
echo "<div class='header-with-button'>";
if ($piano_id) {
    echo "<h2>Esami del Piano: " . htmlspecialchars($piano_info['nome']) . "</h2>";
} else {
    echo "<h2>Tutti gli Esami</h2>";
}

// Pulsante di aggiunta condizionale
if (isset($_SESSION['user_id']) && 
    ($piano_id && verificaPermessiPiano($db, $piano_id) ||
     !$piano_id && isset($_SESSION['is_admin']) && $_SESSION['is_admin'])) {
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Esame</button>";
}
echo "</div>";

// Recupera esami
$stmt = $piano_id ? $esame->readByPiano($piano_id) : $esame->readAll();
$num = $stmt->rowCount();

// Visualizzazione esami
if ($num > 0) {
    echo "<ul class='item-list'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        // Mostra piano di studio solo se stiamo visualizzando tutti gli esami
        $piano_info_display = isset($piano_nome) ? "<div class='item-meta'>Piano: " . htmlspecialchars($piano_nome) . "</div>" : "";
        
        echo "<li>
            <div class='item-title'>" . htmlspecialchars($nome) . "</div>
            <div class='item-meta'>Codice: " . htmlspecialchars($codice) . " | Crediti: {$crediti}</div>
            {$piano_info_display}
            <div class='item-description'>" . htmlspecialchars($descrizione) . "</div>
            <div class='item-actions'>
                <a href='view_pages/view_esame.php?id={$id}'>Visualizza</a> | 
                <a href='argomenti.php?esame_id={$id}'>Argomenti</a>";
        
        // Azioni di modifica/eliminazione condizionali
        // Add debug output to help understand permission check
        $can_modify = verificaPermessiPiano($db, isset($piano_id) ? $piano_id : $piano_id);
        echo "<!-- Permessi Modifica: " . ($can_modify ? 'Sì' : 'No') . " -->";
        
        if (isset($_SESSION['user_id']) && $can_modify) {
            echo " | <a href='?edit={$id}" . ($piano_id ? "&piano_id={$piano_id}" : "") . "'>Modifica</a>";
            echo " | <a href='?delete={$id}" . ($piano_id ? "&piano_id={$piano_id}" : "") . "' onclick='return confirm(\"Sei sicuro di voler eliminare questo esame?\");'>Elimina</a>";
        }
        
        echo "</div></li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nessun esame trovato." . ($piano_id ? " Aggiungi un esame a questo piano di studio." : "") . "</p>";
}

// Debugging for form inclusion
echo "<!-- GET Parameters: ";
print_r($_GET);
echo " -->";
// Sostituisci questa parte
if (isset($_GET['edit'])) {
    include_once 'components/forms/edit_esame.php';
} else {
    include_once 'components/forms/create_esame.php';
}

// Con questa versione temporanea di debug
if (isset($_GET['edit'])) {
    echo "Tentativo di modificare esame ID: " . htmlspecialchars($_GET['edit']);
    
    // Verifica esistenza file
    $edit_form_path = 'components/forms/edit_esame.php';
    if (file_exists($edit_form_path)) {
        echo " - File trovato";
        include_once $edit_form_path;
    } else {
        echo " - ERRORE: File non trovato";
    }
} else {
    include_once 'components/forms/create_esame.php';
}

include_once '../ui/includes/footer.php';
?>