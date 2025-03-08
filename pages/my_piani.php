<?php
// Includi header
include_once '../ui/includes/header.php';

// Se l'utente non è loggato, reindirizza alla pagina di login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Includi file di configurazione e modelli
include_once '../config/database.php';
include_once '../models/piano_di_studio.php';

// Inizializza variabili per messaggi
$message = "";
$message_class = "";

// Connessione al database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    $message = "Problema di connessione al database.";
    $message_class = "error";
} else {
    // Istanza del modello PianoDiStudio
    $piano = new PianoDiStudio($db);

    // --- Gestione del form per creare un nuovo piano di studio ---
    if (isset($_POST['create'])) {
        $piano->nome = $_POST['nome'];
        $piano->descrizione = $_POST['descrizione'];
        $piano->user_id = $_SESSION['user_id'];
        $piano->visibility = $_POST['visibility'];

        if ($piano->create()) {
            $message = "Piano di studio creato con successo!";
            $message_class = "success";
        } else {
            $message = "Impossibile creare il piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della modifica di un piano di studio ---
    if (isset($_POST['update'])) {
        $piano->id = $_POST['id'];
        $piano->nome = $_POST['nome'];
        $piano->descrizione = $_POST['descrizione'];
        $piano->visibility = $_POST['visibility'];

        // Verifica se l'utente è il proprietario
        $owner_info = $piano->readOne();
        if($owner_info && $owner_info['user_id'] == $_SESSION['user_id']) {
            if ($piano->update()) {
                $message = "Piano di studio aggiornato con successo!";
                $message_class = "success";
            } else {
                $message = "Impossibile aggiornare il piano di studio.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per modificare questo piano di studio.";
            $message_class = "error";
        }
    }

    // --- Gestione della cancellazione di un piano ---
    if (isset($_GET['delete'])) {
        $piano->id = $_GET['delete'];
        
        // Verifica se l'utente è il proprietario
        $owner_info = $piano->readOne();
        if($owner_info && $owner_info['user_id'] == $_SESSION['user_id']) {
            if ($piano->delete()) {
                $message = "Piano di studio eliminato con successo!";
                $message_class = "success";
            } else {
                $message = "Impossibile eliminare il piano di studio.";
                $message_class = "error";
            }
        } else {
            $message = "Non hai i permessi per eliminare questo piano di studio.";
            $message_class = "error";
        }
    }

    // Mostra il messaggio se presente
    if (!empty($message)) {
        echo "<div class='message $message_class'>$message</div>";
    }
    
    // --- MOSTRA I PIANI DI STUDIO DELL'UTENTE ---
    echo "<div class='header-with-button'>";
    echo "<h2>I Miei Piani di Studio</h2>";
    echo "<button id='showCreateFormBtn' class='btn-primary'>Aggiungi Nuovo Piano</button>";
    echo "</div>";
    
    // Leggi tutti i piani di studio dell'utente
    $stmt = $piano->readByUser($_SESSION['user_id']);
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        echo "<ul class='item-list'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $visibility_text = ($visibility == 'public') ? "Pubblico" : "Privato";
            
            echo "<li>
                    <div class='item-title'>$nome</div>
                    <div class='item-meta'>Visibilità: $visibility_text</div>
                    <div class='item-description'>$descrizione</div>
                    <div class='item-actions'>
                        <a href='esami.php?piano_id=$id'>Esami</a> | 
                        <a href='?edit=$id'>Modifica</a> | 
                        <a href='?delete=$id' onclick='return confirm(\"Sei sicuro di voler eliminare questo piano?\");'>Elimina</a>
                    </div>
                </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Non hai ancora creato piani di studio.</p>";
    }
    
    // --- FORM DI CREAZIONE/MODIFICA ---
    
    // Form per modificare un piano di studio (mostrato solo quando si clicca "Modifica")
    if (isset($_GET['edit'])) {
        $piano->id = $_GET['edit'];
        $piano_info = $piano->readOne();
        
        // Verifica se l'utente è il proprietario
        if($piano_info && $piano_info['user_id'] == $_SESSION['user_id']) {
            echo "<div id='editFormContainer'>";
            echo "<h2>Modifica Piano di Studio</h2>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='" . $piano->id . "'>";
            echo "<label for='nome'>Nome</label>";
            echo "<input type='text' name='nome' value='" . $piano_info['nome'] . "' required>";
            echo "<label for='descrizione'>Descrizione</label>";
            echo "<textarea name='descrizione' required>" . $piano_info['descrizione'] . "</textarea>";
            
            // Selector for visibility
            echo "<label for='visibility'>Visibilità</label>";
            echo "<select name='visibility'>";
            echo "<option value='private' " . ($piano_info['visibility'] == 'private' ? "selected" : "") . ">Privato</option>";
            echo "<option value='public' " . ($piano_info['visibility'] == 'public' ? "selected" : "") . ">Pubblico</option>";
            echo "</select>";
            
            echo "<button type='submit' name='update'>Aggiorna Piano</button>";
            echo "<a href='my_piani.php' class='btn-secondary'>Annulla</a>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<div class='message error'>Non hai i permessi per modificare questo piano di studio.</div>";
        }
    }
    
    // Form per creare un nuovo piano di studio (inizialmente nascosto)
    echo "<div id='createFormContainer' style='display: none;'>";
    echo "<h2>Crea Nuovo Piano di Studio</h2>";
    echo "<form action='' method='POST'>";
    echo "<label for='nome'>Nome</label>";
    echo "<input type='text' name='nome' required>";
    echo "<label for='descrizione'>Descrizione</label>";
    echo "<textarea name='descrizione' required></textarea>";
    
    // Selector for visibility
    echo "<label for='visibility'>Visibilità</label>";
    echo "<select name='visibility'>";
    echo "<option value='private'>Privato</option>";
    echo "<option value='public'>Pubblico</option>";
    echo "</select>";
    
    echo "<button type='submit' name='create'>Crea Piano</button>";
    echo "<button type='button' id='cancelCreateBtn' class='btn-secondary'>Annulla</button>";
    echo "</form>";
    echo "</div>";
    
    // JavaScript per mostrare/nascondere il form di creazione
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const showCreateFormBtn = document.getElementById('showCreateFormBtn');
            const createFormContainer = document.getElementById('createFormContainer');
            const cancelCreateBtn = document.getElementById('cancelCreateBtn');
            
            if (showCreateFormBtn && createFormContainer) {
                showCreateFormBtn.addEventListener('click', function() {
                    createFormContainer.style.display = 'block';
                    showCreateFormBtn.style.display = 'none';
                });
            }
            
            if (cancelCreateBtn && createFormContainer && showCreateFormBtn) {
                cancelCreateBtn.addEventListener('click', function() {
                    createFormContainer.style.display = 'none';
                    showCreateFormBtn.style.display = 'inline-block';
                });
            }
        });
    </script>";
}

// Includi footer
include_once '../ui/includes/footer.php';
?>